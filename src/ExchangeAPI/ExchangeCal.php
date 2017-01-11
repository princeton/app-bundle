<?php

namespace Princeton\App\ExchangeAPI;

use \jamesiarmes\PhpEws\Autodiscover;
use \jamesiarmes\PhpEws\Client;
use \jamesiarmes\PhpEws\ArrayType\ArrayOfResponseMessagesType;
use \jamesiarmes\PhpEws\ArrayType\ArrayOfStringsType;
use \jamesiarmes\PhpEws\ArrayType\ArrayOfTransitionsGroupsType;
use \jamesiarmes\PhpEws\ArrayType\ArrayOfTransitionsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfPeriodsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfTimeZoneIdType;
use \jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use \jamesiarmes\PhpEws\Enumeration\CalendarItemCreateOrDeleteOperationType;
use \jamesiarmes\PhpEws\Enumeration\CalendarItemUpdateOperationType;
use \jamesiarmes\PhpEws\Enumeration\ConflictResolutionType;
use \jamesiarmes\PhpEws\Enumeration\DayOfWeekIndexType;
use \jamesiarmes\PhpEws\Enumeration\DayOfWeekType;
use \jamesiarmes\PhpEws\Enumeration\DisposalType;
use \jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;
use \jamesiarmes\PhpEws\Enumeration\ExchangeVersionType;
use \jamesiarmes\PhpEws\Enumeration\ImportanceChoicesType;
use \jamesiarmes\PhpEws\Enumeration\ItemClassType;
use \jamesiarmes\PhpEws\Enumeration\Occurrence;
use \jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use \jamesiarmes\PhpEws\Enumeration\SensitivityChoicesType;
use \jamesiarmes\PhpEws\Enumeration\TransitionTargetKindType;
use \jamesiarmes\PhpEws\Enumeration\UnindexedFieldURIType;
use \jamesiarmes\PhpEws\Request\CreateItemType;
use \jamesiarmes\PhpEws\Request\DeleteItemType;
use \jamesiarmes\PhpEws\Request\GetServerTimeZonesType;
use \jamesiarmes\PhpEws\Request\UpdateItemType;
use \jamesiarmes\PhpEws\Type\AbsoluteMonthlyRecurrencePatternType;
use \jamesiarmes\PhpEws\Type\AddressListIdType;
use \jamesiarmes\PhpEws\Type\BodyType;
use \jamesiarmes\PhpEws\Type\CalendarItemType;
use \jamesiarmes\PhpEws\Type\ConnectingSIDType;
use \jamesiarmes\PhpEws\Type\DailyRecurrencePatternType;
use \jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
use \jamesiarmes\PhpEws\Type\EmailAddressType;
use \jamesiarmes\PhpEws\Type\EndDateRecurrenceRangeType;
use \jamesiarmes\PhpEws\Type\ExchangeImpersonationType;
use \jamesiarmes\PhpEws\Type\ItemChangeType;
use \jamesiarmes\PhpEws\Type\ItemIdType;
use \jamesiarmes\PhpEws\Type\PathToUnindexedFieldType;
use \jamesiarmes\PhpEws\Type\PeriodType;
use \jamesiarmes\PhpEws\Type\RecurrenceType;
use \jamesiarmes\PhpEws\Type\RecurringDayTransitionType;
use \jamesiarmes\PhpEws\Type\RelativeMonthlyRecurrencePatternType;
use \jamesiarmes\PhpEws\Type\SetItemFieldType;
use \jamesiarmes\PhpEws\Type\TimeZoneDefinitionType;
use \jamesiarmes\PhpEws\Type\TransitionTargetType;
use \jamesiarmes\PhpEws\Type\TransitionType;
use \jamesiarmes\PhpEws\Type\WeeklyRecurrencePatternType;

/**
 * This class implements some class (static) methods that are useful for dealing with Exchange calendars.
 *
 *
 * @author Kevin Perry, perry@princeton.edu
 * @author Serge J. Goldstein, serge@princeton.edu
 * @author Kelly D. Cole, kellyc@princeton.edu
 * @copyright 2006, 2008, 2013 The Trustees of Princeton University
 * @license For licensing terms, see the license.txt file in the distribution.
 */
class ExchangeCal {
    public static $dayMap = [
        DayOfWeekType::SUNDAY,
        DayOfWeekType::MONDAY,
        DayOfWeekType::TUESDAY,
        DayOfWeekType::WEDNESDAY,
        DayOfWeekType::THURSDAY,
        DayOfWeekType::FRIDAY,
        DayOfWeekType::SATURDAY,
        DayOfWeekType::SUNDAY,
    ];

    public static $weekMap = [
        DayOfWeekIndexType::FIRST,
        DayOfWeekIndexType::SECOND,
        DayOfWeekIndexType::THIRD,
        DayOfWeekIndexType::FOURTH,
        DayOfWeekIndexType::LAST,
    ];
    
    /**
     * @var ExchangeVersionType default to Exchange_2010
     */    
    private $exchangeVersion = Client::VERSION_2010;
 
    /**
     * @var ExchangeCalDelegate
     */
    private $calDelegate;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param ExchangeCalDelegate $calDelegate
     */
    public function __construct(ExchangeCalDelegate $calDelegate)
    {
        $this->calDelegate = $calDelegate;
        $this->logger = $calDelegate->getLogger();
        $this->exchangeVersion = $this->calDelegate->getVersion();
    }

    /**
     * Insert an event into an Exchange calendar.
     *
     * We establish an Exchange client, create an event containing
     * data from the ExchangeEventDelegate, and insert it on the
     * appropriate calendar.
     *
     * @param ExchangeEventDelegate $eventDelegate
     *            the delegate for the event to be created.
     * @return bool True on success.
     */
    public function insertEvent(ExchangeEventDelegate $eventDelegate)
    {
        $status = false;
        try {
            $ews = $this->buildClient();
            if (!$this->isConfigured()) {
                $this->logWarning(
                    "Exchange sync error: not configured for insert of item "
                    . $eventDelegate->getId());
            } elseif (!$ews) {
                $this->logWarning(
                    "Exchange sync error:  unable to create service for insert of item "
                    . $eventDelegate->getId());
            } else {
                // Start building the request.
                $request = new CreateItemType();
                
                $request->Items = new NonEmptyArrayOfAllItemsType();
                $item = $request->Items->CalendarItem[0] = new CalendarItemType();
                
                // Set the subject.
                $item->Subject = $eventDelegate->getSummary();
                
                // Set the start and end times. For Exchange 2007, you need to include the timezone offset.
                // For Exchange 2010, you should set the StartTimeZone and EndTimeZone properties. See below for
                // an example.
                $item->Start = $eventDelegate->getStartDateTime()->format(\DateTime::W3C);
                $item->End = $eventDelegate->getEndDateTime()->format(\DateTime::W3C);
                
                $remind = $eventDelegate->getReminderMinutes();
                if ($remind > 0) {
                    // Specify when reminder is displayed.
                    // If $remind === 0, then this is not set; the default is 15 minutes.
                    $item->ReminderMinutesBeforeStart = $remind;
                } elseif ($remind === false) {
                    // Set no reminders.
                    $item->ReminderIsSet = false;
                }
                
                // Build the body.
                $item->Body = new BodyType();
                $item->Body->BodyType = BodyTypeType::HTML;
                $item->Body->_ = $eventDelegate->getDescription();
                
                // Set the item class type (not required).
                $item->ItemClass = new ItemClassType();
                $item->ItemClass->_ = ItemClassType::APPOINTMENT;
                
                // Set the sensitivity of the event (defaults to normal).
                $item->Sensitivity = new SensitivityChoicesType();
                $item->Sensitivity->_ = SensitivityChoicesType::NORMAL;
                
                // Add some categories to the event.
                $item->Categories = new ArrayOfStringsType();
                $item->Categories->String = array(
                    'Timeline'
                );
                
                // Set the importance of the event.
                $item->Importance = new ImportanceChoicesType();
                $item->Importance->_ = $eventDelegate->getEwsImportance();
                
                $rfc2445method = 'getRfc2445';
                if (method_exists($eventDelegate, $rfc2445method)) {
                    $rfc2445 = $eventDelegate->{$rfc2445method}();
                    if ($rfc2445) {
                    	$item->MimeContent = base64_encode($rfc2445);
                    }
                }
                
                $recurData = $eventDelegate->getEwsRecurrence();
                $this->logWarning("outside recurData=" . print_r($recurData,true));
                
                if ($recurData) {
                    $item->Recurrence = $this->buildRecurrence($recurData);
                    
                    // TODO Deal with deletions.
                }
                
                // Point to the target shared calendar.
                $folder = new \jamesiarmes\PhpEws\Type\TargetFolderIdType;
                $folder->AddressListId = new AddressListIdType();
                $folder->AddressListId->Id = 'Timeline';
                $folder->DistinguishedFolderId = new DistinguishedFolderIdType();
                $folder->DistinguishedFolderId->Id = DistinguishedFolderIdNameType::CALENDAR;
                $folder->DistinguishedFolderId->Mailbox = new EmailAddressType();
                
                $tempEmail = $this->calDelegate->getCalendarMailbox();
                $this->logWarning("ExchangeCal::insertEvent() setting EmailAddress to [$tempEmail]");
                $folder->DistinguishedFolderId->Mailbox->EmailAddress = $this->calDelegate->getCalendarMailbox();
                $request->SavedItemFolderId = $folder;
                
                // Don't send meeting invitations.
                $request->SendMeetingInvitations = CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;

                /* Now save the appointment into Exchange */
                /* @var $response \PhpEws\DataType\CreateItemResponseType */
                $this->logWarning("ExchangeCal::insertEvent() calling CreateItem");
                $response = $ews->CreateItem($request);
                $this->logWarning("ExchangeCal::insertEvent() after calling CreateItem");                
                
                $response_messages = $response->ResponseMessages->CreateItemResponseMessage;
                foreach ($response_messages as $response_message) {
                    // Make sure the request succeeded.
                    if ($response_message->ResponseClass == ResponseClassType::SUCCESS) {
                        $this->logWarning("ExchangeCal::insertEvent() SUCCESS");
                        $itemId = $response_message->Items->CalendarItem[0]->ItemId;
                        $eventDelegate->setEwsId($itemId->Id);
                        $eventDelegate->setEwsChangeKey($itemId->ChangeKey);
                        $status = true;
                        break;
                    } else {
                        $this->logWarning(print_r($response, true));
                        $code = $response_message->ResponseCode;
                        $message = $response_message->MessageText;
                        $this->logWarning("Event FAILED to create with code \"$code\" msg \"$message\".\n");
                        continue;
                    }
                }
            }
        } catch (\Exception $ex) {
            $this->logWarning(
                "Exchange sync error inserting event for item "
                . $eventDelegate->getId() . " "
                //. " on calendar $calId: "
                . $ex->getMessage()
            );
        }
        
        return $status;
    }

    /**
     * Update an event in a Exchange calendar.
     *
     * We build an Exchange client, create an event containing
     * data from the ExchangeEventDelegate, and update the
     * appropriate calendar event item.
     *
     * @param ExchangeEventDelegate $eventDelegate
     *            the delegate for the event to be updated.
     * @return bool True on success
     */
    public function updateEvent(ExchangeEventDelegate $eventDelegate)
    {
        $this->logWarning("ExchangeCal::updateEvent()");
        $status = false;
        try {
            $ews = $this->buildClient();
            if (!$this->isConfigured()) {
                $this->logWarning(
                    "Exchange sync error: not configured for update of item "
                    . $eventDelegate->getId());
            } elseif (!$ews) {
                $this->logWarning(
                    "Exchange sync error:  unable to create service for update of item "
                    . $eventDelegate->getId());
            } else {
                // Start building the request
                $request = new UpdateItemType();
                $request->ConflictResolution = ConflictResolutionType::ALWAYS_OVERWRITE;
                $request->SendMeetingInvitationsOrCancellations = CalendarItemUpdateOperationType::SEND_ONLY_TO_ALL;
                $request->ItemChanges = array();
                
                $change = new ItemChangeType();
                $change->ItemId = new ItemIdType();
                $change->ItemId->Id = $eventDelegate->getEwsId();
                $change->ItemId->ChangeKey = $eventDelegate->getEwsChangeKey();
                $request->ItemChanges[] = $change;
                
                // Update Subject Property
                $field = new SetItemFieldType();
                $field->FieldURI = new PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = UnindexedFieldURIType::ITEM_SUBJECT;
                $field->CalendarItem = new CalendarItemType();
                $field->CalendarItem->Subject = $eventDelegate->getSummary();
                $change->Updates->SetItemField[] = $field;
                
                // Update Start Property
                $field = new SetItemFieldType();
                $field->FieldURI = new PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = UnindexedFieldURIType::CALENDAR_START;
                $field->CalendarItem = new CalendarItemType();
                $field->CalendarItem->Start = $eventDelegate->getStartDateTime()->format(\DateTime::W3C);
                $change->Updates->SetItemField[] = $field;
                
                // Update End Property
                $field = new SetItemFieldType();
                $field->FieldURI = new PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = UnindexedFieldURIType::CALENDAR_END;
                $field->CalendarItem = new CalendarItemType();
                $field->CalendarItem->End = $eventDelegate->getEndDateTime()->format(\DateTime::W3C);
                $change->Updates->SetItemField[] = $field;
                
                // Update Recurrence property
                // TODO Test - do we need to update each Recurrence sub-field separately?
                $recurData = $eventDelegate->getEwsRecurrence();
                if ($recurData) {
                    $field = new SetItemFieldType();
                    $field->FieldURI = new PathToUnindexedFieldType();
                    $field->FieldURI->FieldURI = UnindexedFieldURIType::CALENDAR_RECURRENCE;
                    $field->CalendarItem = new CalendarItemType();
                    $field->CalendarItem->Recurrence = $this->buildRecurrence($recurData);
                    $change->Updates->SetItemField[] = $field;
                    
                    // TODO Deal with exceptions.
                }
                
                // Update the body
                $field = new SetItemFieldType();
                $field->FieldURI = new PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = UnindexedFieldURIType::ITEM_BODY;
                $field->CalendarItem = new CalendarItemType();
                $field->CalendarItem->Body = new BodyType();
                $field->CalendarItem->Body->BodyType = BodyTypeType::HTML;
                $field->CalendarItem->Body->_ = $eventDelegate->getDescription();
                $change->Updates->SetItemField[] = $field;
                
                // Make the change.
                $response = @$ews->UpdateItem($request); 
                
                $response_messages = $response->ResponseMessages->UpdateItemResponseMessage;
                foreach ($response_messages as $response_message) {
                    // Make sure the request succeeded.
                    if ($response_message->ResponseClass == ResponseClassType::SUCCESS) {
                        $this->logWarning("ExchangeCal::updateEvent() SUCCESS");
                        
                        // Iterate over the updated events, printing the id of each.
                        foreach ($response_message->Items->CalendarItem as $item) {
                            // Reset the change key
                            // $app->eid = $response->ResponseMessages->CreateItemResponseMessage->Items->CalendarItem->ItemId->Id;
                            // $app->eid = $item->ItemId->Id;
                            $eventDelegate->setEwsChangeKey($item->ItemId->ChangeKey);
                            $status = true;
                            break;
                        }
                        
                    } else {
                        $this->logWarning(print_r($response, true));
                        $code = $response_message->ResponseCode;
                        $message = $response_message->MessageText;
                        $this->logWarning("Event FAILED to UPDATE with code \"$code\" msg \"$message\".\n");
                        continue;
                    }
                }
            }
        } catch (\Exception $ex) {
            $this->logWarning(
                "Exchange sync error updating event for item "
                . $eventDelegate->getId()
                //. " on calendar $calId: "
                . $ex->getMessage()
            );
        }
        
        return $status;
    }

    /**
     * Delete an event from an Exchange calendar.
     *
     * We establish an Exchange client, and use it to
     * delete the appropriate calendar event item.
     *
     * @param ExchangeEventDelegate $eventDelegate
     *            the delegate for the event to be deleted.
     * @return bool True on success
     */
    public function deleteEvent(ExchangeEventDelegate $eventDelegate)
    {
        $this->logWarning("ExchangeCal::deleteEvent()");
        $status = false;
        try {
            $ews = $this->buildClient();
            if (!$this->isConfigured()) {
                $this->logWarning(
                    "Exchange sync error: not configured for delete of item "
                    . $eventDelegate->getId());
            } elseif (!$ews) {
                $this->logWarning(
                    "Exchange sync error:  unable to create service for delete of item "
                    . $eventDelegate->getId());
            } else {
                $this->logWarning("ExchangeCal::deleteEvent() after buildClient()");
                $request = new DeleteItemType();
    
                // Send to trash can, or useDisposalType::HARD_DELETE instead to bypass the bin directly.
                // Have to set to HARD_DELETE in order to work on Outlook 365
                $request->DeleteType = DisposalType::HARD_DELETE;
                // Inform no one who shares the item that it has been deleted.
                $request->SendMeetingCancellations = CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;
    
                // Set the item to be deleted.
                $item = new ItemIdType();
                $item->Id = $eventDelegate->getEwsId();
                $item->ChangeKey = $eventDelegate->getEwsChangeKey();
    
                // We can use this to mass delete but in this case it's just one item.
                $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();
                $request->ItemIds->ItemId = $item;
                
                // Send the delete request
                $this->logWarning("ExchangeCal::deleteEvent() before DeleteItem()");
                $response = $ews->DeleteItem($request);
                $this->logWarning("ExchangeCal::deleteEvent() after DeleteItem()");
                
                $response_messages = $response->ResponseMessages->DeleteItemResponseMessage;
                foreach ($response_messages as $response_message) {
                    // Make sure the request succeeded.
                    if ($response_message->ResponseClass == ResponseClassType::SUCCESS) {
                        $this->logWarning("ExchangeCal::deleteEvent() SUCCESS");
                        $status = true;
                        $eventDelegate->setEwsId(null);
                        $eventDelegate->setEwsChangeKey(null);
                        break;
                    } else {
                        $this->logWarning(print_r($response, true));
                        $code = $response_message->ResponseCode;
                        $message = $response_message->MessageText;
                        $this->logWarning("Event FAILED to create with code \"$code\" msg \"$message\".\n");
                        continue;
                    }
                }
            }
        } catch (\Exception $ex) {
            $this->logWarning(
                "Exchange sync error deleting event for item "
                . $eventDelegate->getId()
                //. " on calendar $calId: "
                . $ex->getMessage()
                );
        }
    
        return $status;
    }
    

    /**
     * We must have both a calendar id and an access token
     * in order to be properly configured.
     */
    protected function isConfigured()
    {
        return true;
    }

    /**
     * Create an Exchange client object.
     *
     * @return mixed
     */
    protected function buildClient()
    {
        $client = false;
        
        $email = $this->calDelegate->getEmail();
        $password = $this->calDelegate->getPassword();
        
        $this->logWarning("ExchangeCal::buildClient() email=$email password=$password");
         
        if ($email && $password) {
            $this->logWarning("ExchangeCal::buildClient() trying auto-discovery");
            // Try auto-discovery
            try {
                $client = Autodiscover::getEWS($email, $password);
                
                //Explicitly setting server version to work around bug with autodiscover returning incorrect version for recurrence events.
                $client->setVersion($this->exchangeVersion);
            } catch(\Exception $ex) {
                $client = false;
            }
            
        }
        
        $this->logWarning("ExchangeCal::buildClient() after auto-discovery ");
        
        // If auto-discovery failed, try regular login.
        if (!$client) {
            $this->logWarning("ExchangeCal::buildClient() trying regular login");
            
            $host = $this->calDelegate->getHostname();
            $username = $this->calDelegate->getUsername();
            
            $this->logWarning("ExchangeCal::buildClient() ExchangeVersion=$this->exchangeVersion host=$host username=$username password=$password");
            
            if ($host && $username && $password) {
                $client = new Client($host, $username, $password, $this->exchangeVersion);
            }
        }
        
        //PYH test
        //$timezone = 'Eastern Standard Time';
        //$client->setTimezone($timezone);
        
        $this->logWarning("ExchangeCal::buildClient() client= " . print_r($client,true));
        
        return $client;
    }

    /**
     * Note that this code is a little odd because the php-ews library
     * mis-declares the classes that should be subclassed from other subclasses.
     *
     * @see http://stackoverflow.com/questions/23815461/creating-a-recurring-calendar-event-with-php-ews
     */
    protected function buildRecurrence($recurData)
    {
        $this->logWarning("buildRecurrence recurData =" . print_r($recurData, true));
        $item = new RecurrenceType();
        $range = new EndDateRecurrenceRangeType();
        $range->EndDate = $recurData['endDate'];
        /* @var $range \PhpEws\DataType\RecurrenceRangeBaseType */
        $range->StartDate = $recurData['startDate'];
        $item->EndDateRecurrence = $range;
        
        $period = $recurData['period'];
        
        /* @var $date \DateTime */
        if (strlen($recurData['startDate']) == 10) {
            $date = \DateTime::createFromFormat('Y-m-d', $recurData['startDate']);
        } else {
            $date = \DateTime::createFromFormat(DATE_ISO8601, $recurData['startDate']);
        }
        $this->logWarning("buildRecurrence Date = " . $date->format(DATE_ISO8601));
        $this->logWarning("buildRecurrence Date(w) = " . $date->format('w'));
        
        switch ($period) {
            case 'daily':
                $recurrence = new DailyRecurrencePatternType();
                $recurrence->Interval = $recurData['interval'];
                $item->DailyRecurrence = $recurrence;
                break;
                
            case 'weekly':
                $recurrence = new WeeklyRecurrencePatternType();
                $recurrence->Interval = $recurData['interval'];
                $item->WeeklyRecurrence = $recurrence;
                
                /* @var $recurrence \PhpEws\DataType\FirstWeeklyRecurrencePatternType */
                $recurrence->FirstDayOfWeek = self::$dayMap[intval($date->format('w'))];
                $recurrence->DaysOfWeek = new ArrayOfStringsType();
                $recurrence->DaysOfWeek = array_map(
                    function ($day) {
                        return ExchangeCal::$dayMap[$day];
                    },
                    $recurData['days']
                    );
                break;
                
            case 'monthly':
                $recurrence = new AbsoluteMonthlyRecurrencePatternType();
                $recurrence->Interval = $recurData['interval'];
                $item->AbsoluteMonthlyRecurrence = $recurrence;
                
                /* @var $recurrence \PhpEws\DataType\AbsoluteMonthlyRecurrencePatternType */
                $recurrence->DayOfMonth = intval($date->format('j'));
                break;
                
            case 'relmonthly':
                $recurrence = new RelativeMonthlyRecurrencePatternType();
                $recurrence->Interval = $recurData['interval'];
                $item->RelativeMonthlyRecurrence = $recurrence;
                
                /* @var $recurrence \PhpEws\DataType\RelativeMonthlyRecurrencePatternType */
                $recurrence->DayOfWeekIndex = self::$weekMap[intval(floor(($date->format('j') - 1) / 7))];
                $recurrence->DaysOfWeek = self::$dayMap[intval($date->format('w'))];
                break;
                
            case 'yearly':
            case 'relyearly':
                throw new \UnexpectedValueException('Yearly recurrence not implemented.');
                
            default:
                throw new \UnexpectedValueException('Invalid recurrence data.');
        }
        
        return $item;
    }
    
    /**
     * Sets the impersonation property of the EWS Exchange client.
     *
     * Updates an existing Exchange client, seting the impersonation to
     * the target account..
     *
     * @param PhpEws\Client $ewsClient
     *            the delegate for the event to be created.
     * @param String $targetSmtpAddress
     *            the full primary email address of the exchange account to impersonate.
     *            
     */
    protected function setImpersonation($ewsClient, $targetSmtpAddress)
    {
        $ei = new ExchangeImpersonationType();
        $sid = new ConnectingSIDType();
        $sid->PrimarySmtpAddress = $targetSmtpAddress;
        $ei->ConnectingSID = $sid;
        $ewsClient->setImpersonation($ei);
    }
    
    /**
     * Retrieves the Time Zone definitions from the Exchange server.
     *
     * Updates an existing Exchange client, seting the impersonation to
     * the target account..
     *
     * @param String $timeZoneId
     *            Id the timezone to retrieve 'Eastern Standard Time'.
     *            
     * @return mixed - false - if no definition is found
     *               - PhpEws\Type\TimeZoneDefinitionType - if a definition is found           
     */
    protected function getTimeZoneDefs($timeZoneId)
    {
        $returnVal = false;
        $host = $this->calDelegate->getHostname();
        $user = $this->calDelegate->getUsername();
        $pass = $this->calDelegate->getPassword();
        $version = $this->exchangeVersion;
        
        $ews = new Client($host, $user, $pass, $version);
        
        $request = new GetServerTimeZonesType();
        $request->Ids = new NonEmptyArrayOfTimeZoneIdType();
        $request->Ids->Id[] = $timeZoneId;
        
        $response = $ews->GetServerTimeZones($request);
        
        $tzresponse_messages = $response->ResponseMessages->GetServerTimeZonesResponseMessage;
        foreach ($tz_msg as $tzresponse_messages) {
            $tzds = $tz_msg->TimeZoneDefinitions;
            foreach ($tzd as $tzds) {
                $returnVal = $tzd->TimeZoneDefinition;
                break;
            }
        }
        
        return $returnVal;
    }
    
    /**
     * Builds and populate the StartTimeZone and EndTimeZone of a calendar event.
     * Currently does this for Eastern Standard Time timezone only.
     * This is for Exchange 2010 and above only. 
     *
     * @param PhpEws\Type\CalendarItemType $item
     *            The calendar event.
     *
     */
    protected function buildTimeZone($item)
    {
        // Build the timezone definition and set it as the StartTimeZone.
        $item->StartTimeZone = new TimeZoneDefinitionType();
        $item->StartTimeZone->Id = 'Eastern Standard Time';
        $item->StartTimeZone->Periods = new NonEmptyArrayOfPeriodsType();
        
        $period = new PeriodType();
        $period->Bias =  'PT5H';
        $period->Name = 'Standard';
        $period->Id = 'trule:Microsoft/Registry/Eastern Standard Time/2006-Standard';
        $item->StartTimeZone->Periods->Period[] = $period;
        
        $period = new PeriodType();
        $period->Bias =  'PT4H';
        $period->Name = 'Daylight';
        $period->Id = 'trule:Microsoft/Registry/Eastern Standard Time/2006-Daylight';
        $item->StartTimeZone->Periods->Period[] = $period;
        
        $period = new PeriodType();
        $period->Bias =  'PT5H';
        $period->Name = 'Standard';
        $period->Id = 'trule:Microsoft/Registry/Eastern Standard Time/2007-Standard';
        $item->StartTimeZone->Periods->Period[] = $period;
        
        $period = new PeriodType();
        $period->Bias =  'PT4H';
        $period->Name = 'Daylight';
        $period->Id = 'trule:Microsoft/Registry/Eastern Standard Time/2007-Daylight';
        $item->StartTimeZone->Periods->Period[] = $period;
        
        $item->StartTimeZone->TransitionsGroups = new ArrayOfTransitionsGroupsType();
        $item->StartTimeZone->TransitionsGroups->TransitionsGroup = array();
        
        $group = new ArrayOfTransitionsGroupsType();
        $group->Id = 0;
        
        $transition = new RecurringDayTransitionType();
        $transition->To = new TransitionTargetType();
        $transition->To->_ = 'trule:Microsoft/Registry/Eastern Standard Time/2006-Daylight';
        $transition->To->Kind = new TransitionTargetKindType();
        $transition->To->Kind->_ = TransitionTargetKindType::PERIOD;
        $transition->TimeOffset = 'PT2H';
        $transition->Month = 4;
        $transition->Occurrence = new Occurrence();
        $transition->Occurrence->_ = Occurrence::FIRST_FROM_BEGINNING;
        $transition->DayOfWeek = new DayOfWeekType();
        $transition->DayOfWeek->_ = DayOfWeekType::SUNDAY;
        $group->RecurringDayTransition[] = $transition;
        
        $transition = new RecurringDayTransitionType();
        $transition->To = new TransitionTargetType();
        $transition->To->_ = 'trule:Microsoft/Registry/Eastern Standard Time/2006-Standard';
        $transition->To->Kind = new TransitionTargetKindType();
        $transition->To->Kind->_ = TransitionTargetKindType::PERIOD;
        $transition->TimeOffset = 'PT2H';
        $transition->Month = 10;
        $transition->Occurrence = new Occurrence();
        $transition->Occurrence->_ = Occurrence::FIRST_FROM_END;
        $transition->DayOfWeek = new DayOfWeekType();
        $transition->DayOfWeek->_ = DayOfWeekType::SUNDAY;
        $group->RecurringDayTransition[] = $transition;
        $item->StartTimeZone->TransitionsGroups->TransitionsGroup[] = $group;
        
        $group = new ArrayOfTransitionsGroupsType();
        $group->Id = 1;
        
        $transition = new RecurringDayTransitionType();
        $transition->To = new TransitionTargetType();
        $transition->To->_ = 'trule:Microsoft/Registry/Eastern Standard Time/2006-Daylight';
        $transition->To->Kind = new TransitionTargetKindType();
        $transition->To->Kind->_ = TransitionTargetKindType::PERIOD;
        $transition->TimeOffset = 'PT2H';
        $transition->Month = 3;
        $transition->Occurrence = new Occurrence();
        $transition->Occurrence->_ = Occurrence::FIRST_FROM_BEGINNING;
        $transition->DayOfWeek = new DayOfWeekType();
        $transition->DayOfWeek->_ = DayOfWeekType::SUNDAY;
        $group->RecurringDayTransition[] = $transition;
        
        $transition = new RecurringDayTransitionType();
        $transition->To = new TransitionTargetType();
        $transition->To->_ = 'trule:Microsoft/Registry/Eastern Standard Time/2006-Standard';
        $transition->To->Kind = new TransitionTargetKindType();
        $transition->To->Kind->_ = TransitionTargetKindType::PERIOD;
        $transition->TimeOffset = 'PT2H';
        $transition->Month = 11;
        $transition->Occurrence = new Occurrence();
        $transition->Occurrence->_ = Occurrence::FIRST_FROM_END;
        $transition->DayOfWeek = new DayOfWeekType();
        $transition->DayOfWeek->_ = DayOfWeekType::SUNDAY;
        $group->RecurringDayTransition[] = $transition;
        $item->StartTimeZone->TransitionsGroups->TransitionsGroup[] = $group;
        
        $item->StartTimeZone->Transitions = new ArrayOfTransitionsType();
        $item->StartTimeZone->Transitions->Transition = new TransitionType();
        $item->StartTimeZone->Transitions->Transition->To = new TransitionTargetType();
        $item->StartTimeZone->Transitions->Transition->To->_ = 0;
        $item->StartTimeZone->Transitions->Transition->To->Kind = new TransitionTargetKindType();
        $item->StartTimeZone->Transitions->Transition->To->Kind = TransitionTargetKindType::GROUP;
        
        $item->EndTimeZone = clone $item->StartTimeZone;
    }
    
    protected function logWarning($message)
    {
        if ($this->logger) {
            $this->logger->warning($message);
        }
    }
}
