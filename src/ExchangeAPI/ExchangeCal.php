<?php

namespace Princeton\App\ExchangeAPI;

use DateTime;
use UnexpectedValueException;
use jamesiarmes\PhpEws\Autodiscover;
use jamesiarmes\PhpEws\Client;
use jamesiarmes\PhpEws\ArrayType\ArrayOfStringsType;
use jamesiarmes\PhpEws\ArrayType\ArrayOfTransitionsGroupsType;
use jamesiarmes\PhpEws\ArrayType\ArrayOfTransitionsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseFolderIdsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfPathsToElementType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfPeriodsType;
use jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfTimeZoneIdType;
use jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use jamesiarmes\PhpEws\Enumeration\CalendarItemCreateOrDeleteOperationType;
use jamesiarmes\PhpEws\Enumeration\CalendarItemTypeType;
use jamesiarmes\PhpEws\Enumeration\CalendarItemUpdateOperationType;
use jamesiarmes\PhpEws\Enumeration\ConflictResolutionType;
use jamesiarmes\PhpEws\Enumeration\DayOfWeekIndexType;
use jamesiarmes\PhpEws\Enumeration\DayOfWeekType;
use jamesiarmes\PhpEws\Enumeration\DefaultShapeNamesType;
use jamesiarmes\PhpEws\Enumeration\DisposalType;
use jamesiarmes\PhpEws\Enumeration\DistinguishedFolderIdNameType;
use jamesiarmes\PhpEws\Enumeration\ExchangeVersionType;
use jamesiarmes\PhpEws\Enumeration\ImportanceChoicesType;
use jamesiarmes\PhpEws\Enumeration\ItemClassType;
use jamesiarmes\PhpEws\Enumeration\ItemQueryTraversalType;
use jamesiarmes\PhpEws\Enumeration\Occurrence;
use jamesiarmes\PhpEws\Enumeration\ResponseClassType;
use jamesiarmes\PhpEws\Enumeration\SensitivityChoicesType;
use jamesiarmes\PhpEws\Enumeration\TransitionTargetKindType;
use jamesiarmes\PhpEws\Enumeration\UnindexedFieldURIType;
use jamesiarmes\PhpEws\Request\CreateItemType;
use jamesiarmes\PhpEws\Request\DeleteItemType;
use jamesiarmes\PhpEws\Request\FindItemType;
use jamesiarmes\PhpEws\Request\GetItemType;
use jamesiarmes\PhpEws\Request\GetServerTimeZonesType;
use jamesiarmes\PhpEws\Request\UpdateItemType;
use jamesiarmes\PhpEws\Type\AbsoluteMonthlyRecurrencePatternType;
use jamesiarmes\PhpEws\Type\AddressListIdType;
use jamesiarmes\PhpEws\Type\BodyType;
use jamesiarmes\PhpEws\Type\CalendarItemType;
use jamesiarmes\PhpEws\Type\CalendarViewType;
use jamesiarmes\PhpEws\Type\ConnectingSIDType;
use jamesiarmes\PhpEws\Type\DailyRecurrencePatternType;
use jamesiarmes\PhpEws\Type\DistinguishedFolderIdType;
use jamesiarmes\PhpEws\Type\EmailAddressType;
use jamesiarmes\PhpEws\Type\EndDateRecurrenceRangeType;
use jamesiarmes\PhpEws\Type\ExchangeImpersonationType;
use jamesiarmes\PhpEws\Type\ItemChangeType;
use jamesiarmes\PhpEws\Type\ItemIdType;
use jamesiarmes\PhpEws\Type\ItemResponseShapeType;
use jamesiarmes\PhpEws\Type\PathToUnindexedFieldType;
use jamesiarmes\PhpEws\Type\PeriodType;
use jamesiarmes\PhpEws\Type\RecurrenceType;
use jamesiarmes\PhpEws\Type\RecurringDayTransitionType;
use jamesiarmes\PhpEws\Type\RecurringMasterItemIdType;
use jamesiarmes\PhpEws\Type\RelativeMonthlyRecurrencePatternType;
use jamesiarmes\PhpEws\Type\SetItemFieldType;
use jamesiarmes\PhpEws\Type\TimeZoneDefinitionType;
use jamesiarmes\PhpEws\Type\TransitionTargetType;
use jamesiarmes\PhpEws\Type\TransitionType;
use jamesiarmes\PhpEws\Type\WeeklyRecurrencePatternType;
use jamesiarmes\PhpEws\Type\TargetFolderIdType;

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
        $this->logWarning("ExchangeCal::insertEvent()");
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
                $item->Start = $eventDelegate->getStartDateTime()->format(DateTime::W3C);
                $item->End = $eventDelegate->getEndDateTime()->format(DateTime::W3C);
                $item->IsAllDayEvent = $this->isAllDay($eventDelegate);

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
                if ($recurData) {
                    $item->Recurrence = $this->buildRecurrence($recurData);
                    // Recurrence exclusions are processed later after the event is created.
                }

                // Point to the target calendar of event owner.
                $folder = new TargetFolderIdType;
                $folder->AddressListId = new AddressListIdType();
                $folder->AddressListId->Id = 'Timeline';
                $folder->DistinguishedFolderId = new DistinguishedFolderIdType();
                $folder->DistinguishedFolderId->Id = DistinguishedFolderIdNameType::CALENDAR;
                $folder->DistinguishedFolderId->Mailbox = new EmailAddressType();
                $folder->DistinguishedFolderId->Mailbox->EmailAddress = $this->calDelegate->getCalendarMailbox();

                $request->SavedItemFolderId = $folder;

                // Don't send meeting invitations.
                $request->SendMeetingInvitations = CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;

                /* Now save the appointment into Exchange */
                /* @var $response \PhpEws\DataType\CreateItemResponseType */
                $this->logWarning("ExchangeCal::insertEvent() calling CreateItem");
                $response = $ews->CreateItem($request);

                $response_messages = $response->ResponseMessages->CreateItemResponseMessage;
                foreach ($response_messages as $response_message) {
                    // Make sure the request succeeded.
                    if ($response_message->ResponseClass == ResponseClassType::SUCCESS) {
                        $this->logWarning("ExchangeCal::insertEvent() SUCCESS");
                        $itemId = $response_message->Items->CalendarItem[0]->ItemId;
                        $eventDelegate->setEwsId($itemId->Id);
                        $eventDelegate->setEwsChangeKey($itemId->ChangeKey);
                        $status = true;

                        // now handle exclusions
                        if ($recurData) {
                            $this->processRecurrencExclusions($ews, $recurData, $itemId, $eventDelegate);
                        }

                        break;
                    } else {
                        $code = $response_message->ResponseCode;
                        $message = $response_message->MessageText;
                        $this->logWarning("Event FAILED to CREATE with code \"$code\" msg \"$message\".\n");
                        $this->logWarning("Response = " . print_r($response, true));
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

                // Update IsAllDayEvent Property
                $field = new SetItemFieldType();
                $field->FieldURI = new PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = UnindexedFieldURIType::CALENDAR_IS_ALL_DAY_EVENT;
                $field->CalendarItem = new CalendarItemType();
                $field->CalendarItem->IsAllDayEvent = $this->isAllDay($eventDelegate);
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
                            // now handle exceptions
                            if ($recurData) {
                                $this->processRecurrencExclusions($ews, $recurData, $change->ItemId, $eventDelegate);
                            }
                            $status = true;
                            break;
                        }

                    } else {
                        $code = $response_message->ResponseCode;
                        $message = $response_message->MessageText;
                        $this->logWarning("Event FAILED to UPDATE with code \"$code\" msg \"$message\".\n");
                        $this->logWarning("Response = " . print_r($response, true));
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

                        $code = $response_message->ResponseCode;
                        $message = $response_message->MessageText;
                        $this->logWarning("Event FAILED to DELETE with code \"$code\" msg \"$message\".\n");
                        $this->logWarning("Response = " . print_r($response, true));
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
                /* @var $recurrence jamesiarmes\PhpEws\Type\AbsoluteMonthlyRecurrencePatternType */
                $recurrence = new AbsoluteMonthlyRecurrencePatternType();
                $recurrence->Interval = $recurData['interval'];
                $item->AbsoluteMonthlyRecurrence = $recurrence;

                /* @var $recurrence \PhpEws\DataType\AbsoluteMonthlyRecurrencePatternType */
                $recurrence->DayOfMonth = intval($date->format('j'));
                break;

            case 'relmonthly':
                /* @var $recurrence jamesiarmes\PhpEws\Type\RelativeMonthlyRecurrencePatternType */
                $recurrence = new RelativeMonthlyRecurrencePatternType();
                $recurrence->Interval = $recurData['interval'];
                $item->RelativeMonthlyRecurrence = $recurrence;

                /* @var $recurrence \PhpEws\DataType\RelativeMonthlyRecurrencePatternType */
                $recurrence->DayOfWeekIndex = self::$weekMap[intval(floor(($date->format('j') - 1) / 7))];
                $recurrence->DaysOfWeek = self::$dayMap[intval($date->format('w'))];
                break;

            case 'yearly':
            case 'relyearly':
                throw new UnexpectedValueException('Yearly recurrence not implemented.');

            default:
                throw new UnexpectedValueException('Invalid recurrence data.');
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
        foreach ($tzresponse_messages as $tz_msg) {
            $tzds = $tz_msg->TimeZoneDefinitions;
            foreach ($tzds as $tzd) {
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

        /* @var $group jamesiarmes\PhpEws\Type\ArrayOfTransitionsGroupsType */
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


    /**
     * @param ExchangeEventDelegate $eventDelegate
     * @return bool True if event spans entire day.
     */
    protected function isAllDay($eventDelegate) {
        return (
            $eventDelegate->getStartDateTime()->format('Hi') === '0000'
            && $eventDelegate->getEndDateTime()->format('Hi') === '2359'
        );
    }

    protected function processRecurrencExclusions($ews, $recurData, $recurringMasterItemId, $eventDelegate)
    {
        $this->logWarning("processRecurrencExclusions");
        //$this->logWarning(print_r($response, true));
        $numDeletes = count($recurData['deletions']);
        if ($numDeletes > 0) {
            $deletions = $recurData['deletions'];
            $startDate = '2100-01-01';
            $endDate = '1900-01-01';

            foreach ($deletions as $deletion) {
                $this->logWarning("Exclusion date = $deletion");
                if ($deletion < $startDate) {
                    $startDate = $deletion;
                }
                if ($deletion > $endDate) {
                    $endDate = $deletion;
                }
            }

            $this->logWarning("processRecurrencExclusions - minDate = $startDate maxDate = $endDate");

            // now we have the range to get occurrences.
            // load occurrences and validate
            // validate master for each valid occurence
            // validate date and remove
            $response = $this->getOccurrenceByDateRange($ews, $recurData, $startDate, $endDate);
            $this->validateAndRemoveOccurrence($ews, $response, $deletions, $recurringMasterItemId, $eventDelegate);

        }
        return;
    }

    protected function getOccurrenceByDateRange($ews, $recurData, $startDate, $endDate)
    {
        $this->logWarning("getOccurrenceByDateRange");

        $start_date = new DateTime($startDate);
        $end_date = new DateTime($endDate);

        $start_date->modify('-1 day');
        $end_date->modify('+1 day');

        $request = new FindItemType();
        $request->ParentFolderIds = new NonEmptyArrayOfBaseFolderIdsType();

        // Return all event properties.
        $request->Traversal = ItemQueryTraversalType::SHALLOW;
        $request->ItemShape = new ItemResponseShapeType();
        $request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;

        $folder_id = new DistinguishedFolderIdType();
        $folder_id->Id = DistinguishedFolderIdNameType::CALENDAR;
        $folder_id->Mailbox = new EmailAddressType();
        $folder_id->Mailbox->EmailAddress = $this->calDelegate->getCalendarMailbox();
        $request->ParentFolderIds->DistinguishedFolderId[] = $folder_id;
        $request->CalendarView = new CalendarViewType();
        $request->CalendarView->StartDate = $start_date->format('c');
        $request->CalendarView->EndDate = $end_date->format('c');

        $this->logWarning("getOccurrenceByDateRange - FindItem for range $startDate to $endDate");
        $response = $ews->FindItem($request);
        //$this->logWarning("processRecurrencExclusions - response = " . print_r($response,true));

        $this->logWarning("exit getOccurrenceByDateRange");
        return $response;
    }

    protected function validateAndRemoveOccurrence($ews, $findItemResponse, $deletions, $recurringMasterItemId, $eventDelegate)
    {
        $this->logWarning("validateAndRemoveOccurrence");

        $response_messages = $findItemResponse->ResponseMessages->FindItemResponseMessage;
        foreach ($response_messages as $response_message) {
            // Make sure the request succeeded.
            if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                $message = $response_message->ResponseCode;
                $this->logWarning("Failed to search for events with \"$message\"\n");
                continue;
            }

            // Iterate over the events that were found, printing some data for each.
            $items = $response_message->RootFolder->Items->CalendarItem;
            foreach ($items as $item) {
                //$this->printCalendarItemFull($item);
                // check to see event is an occurrence
                if ($item->CalendarItemType == CalendarItemTypeType::OCCURRENCE) {

                    // check this occurence's recurrence master is the correct one
                    if ($this->isValidOccurrence($ews, $item->ItemId, $recurringMasterItemId)) {
                        // check to see if this occurrence date is in exclusion list
                        // RecurrenceId is the date of the occurrence
                        if ($this->isValidDeletionDate($item->RecurrenceId, $deletions)) {

                            if ($this->deleteOccurrenceById($ews, $item->ItemId, $eventDelegate)) {
                                // Need to update change key on recurring master
                                $this->updateEventDelegateChangeKey($ews, $recurringMasterItemId, $eventDelegate);
                            }
                        }
                    }
                }
            }
        }

        $this->logWarning("exit validateAndRemoveOccurrence");
    }

    protected function isValidDeletionDate($date, $deletions)
    {
        $this->logWarning("isValidDeletionDate");
        $returnVal = false;
        $checkDate = (new DateTime($date))->format('Y-m-d');

        // $deletions is already in Y-m-d format
        foreach ($deletions as $deletion) {
            if ( $checkDate == $deletion) {
                $returnVal = true;
                $this->logWarning("Valid Exclusion Date Found: $checkDate");
                break;
            }
        }

        $this->logWarning("exit isValidDeletionDate");
        return $returnVal;
    }

    protected function isValidOccurrence($ews, $occurrencItemId, $recurringMasterItemId)
    {
        $this->logWarning("isValidOccurrence");
        $returnVal = false;

        /* @var $request jamesiarmes\PhpEws\Type\GetItemType */
        $request = new GetItemType();
        $request->Traversal = ItemQueryTraversalType::SHALLOW;
        $request->ItemShape = new ItemResponseShapeType();
        $request->ItemShape->BaseShape = DefaultShapeNamesType::ID_ONLY;

        $properties = array('item:Subject', 'item:Categories', 'item:DateTimeCreated',
            'item:LastModifiedTime', 'item:Sensitivity', 'item:ItemClass',
            'calendar:Start', 'calendar:End', 'calendar:CalendarItemType',
            'calendar:IsRecurring', 'calendar:Recurrence', 'calendar:FirstOccurrence',
            'calendar:LastOccurrence', 'calendar:ModifiedOccurrences', 'calendar:DeletedOccurrences');
        $request->ItemShape->AdditionalProperties = new NonEmptyArrayOfPathsToElementType();
        foreach ($properties as $p) {
            $entry = new PathToUnindexedFieldType();
            $entry->FieldURI = $p;
            $request->ItemShape->AdditionalProperties->FieldURI[] = $entry;
        }

        $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();
        $request->ItemIds->RecurringMasterItemId = new RecurringMasterItemIdType();
        $request->ItemIds->RecurringMasterItemId->OccurrenceId = $occurrencItemId->Id;

        $this->logWarning("isValidOccurrence - getItem");
        $response = $ews->GetItem($request);
        //$this->logWarning("processRecurrencExclusions - response = " . print_r($response,true));

        $response_messages = $response->ResponseMessages->GetItemResponseMessage;
        foreach ($response_messages as $response_message) {
            $this->logWarning("in foreach after getItem");
            // Make sure the request succeeded.
            if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                $message = $response_message->ResponseCode;
                $this->logWarning("Failed to search for events with \"$message\"\n");
                continue;
            }

            // Iterate over the events that were found, printing some data for each.
            $items = $response_message->Items->CalendarItem;
            foreach ($items as $item) {
                $this->logWarning("in nested foreach after getItem");
                $this->printCalendarItem($item);
                if ($item->CalendarItemType == CalendarItemTypeType::RECURRING_MASTER) {
                    $this->logWarning("Is RECURRING_MASTER");
                    $this->logWarning("MASTER ID = " . $recurringMasterItemId->Id);
                    $this->logWarning("ITEM   ID = " . $item->ItemId->Id);
                    if ($item->ItemId->Id == $recurringMasterItemId->Id) {
                        $returnVal = true;
                        $this->logWarning("Recurring Master Found - returning TRUE");
                        break;
                    } else {
                        $this->logWarning("Recurring Master NOT Found");
                    }
                }
            }
        }

        $this->logWarning("exit isValidOccurrence");

        return $returnVal;
    }

    protected function deleteOccurrenceById($ews, $itemId, $eventDelegate)
    {
        $this->logWarning("ExchangeCal::deleteOccurrenceById [" . $itemId->Id . "]");

        $status = false;
        $request = new DeleteItemType();

        // Send to trash can, or useDisposalType::HARD_DELETE instead to bypass the bin directly.
        // Have to set to HARD_DELETE in order to work on Outlook 365
        $request->DeleteType = DisposalType::HARD_DELETE;
        // Inform no one who shares the item that it has been deleted.
        $request->SendMeetingCancellations = CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;

        // Set the item to be deleted.
        $item = new ItemIdType();
        $item->Id = $itemId->Id;
        $item->ChangeKey = $itemId->ChangeKey;

        // We can use this to mass delete but in this case it's just one item.
        $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();
        $request->ItemIds->ItemId = $item;

        // Send the delete request
        $this->logWarning("ExchangeCal::deleteOccurrenceById() before DeleteItem()");
        $response = $ews->DeleteItem($request);

        $response_messages = $response->ResponseMessages->DeleteItemResponseMessage;
        foreach ($response_messages as $response_message) {
            // Make sure the request succeeded.
            if ($response_message->ResponseClass == ResponseClassType::SUCCESS) {
                $this->logWarning("ExchangeCal::deleteOccurrenceById() SUCCESS");
                $status = true;
                break;
            } else {
                $this->logWarning(print_r($response, true));
                $code = $response_message->ResponseCode;
                $message = $response_message->MessageText;
                $this->logWarning("FAILED to delete occurrence [" . $itemId->Id . "] with code \"$code\" msg \"$message\".\n");
                continue;
            }
        }

        $this->logWarning("exit deleteOccurrenceById [" . $itemId->Id . "]");
        return $status;
    }

    protected function updateEventDelegateChangeKey($ews, $itemId, $eventDelegate)
    {
        $this->logWarning("updateEventDelegateChangeKey");
        // Build the request.
        $request = new GetItemType();
        $request->ItemShape = new ItemResponseShapeType();
        $request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;
        $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();

        // Iterate over the event ids, setting each one on the request.
        $item = new ItemIdType();
        $item->Id = $itemId->Id;
        $request->ItemIds->ItemId[] = $item;

        $response = $ews->GetItem($request);

        // Iterate over the results, printing any error messages or event names.
        $response_messages = $response->ResponseMessages->GetItemResponseMessage;
        foreach ($response_messages as $response_message) {
            // Make sure the request succeeded.
            if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                $message = $response_message->ResponseCode;
                $this->logWarning("updateEventDelegateChangeKey - Failed to get event with \"$message\"\n");
                continue;
            }

            // Iterate over the events, printing the title for each.
            /* @var $item jamesiarmes\PhpEws\Type\CalendarItemType */
            foreach ($response_message->Items->CalendarItem as $item) {
                $this->logWarning("Checking IDs item->Id =" . $item->ItemId->Id);
                $this->logWarning("Checking IDs Master->Id =" . $itemId->Id);
                if ($item->ItemId->Id == $itemId->Id){
                    $this->logWarning("updating Recurring Master Change Key");
                    $eventDelegate->setEwsChangeKey($item->ItemId->ChangeKey);
                    break;
                }
            }
        }

        $this->logWarning("exit updateEventDelegateChangeKey");
    }

    protected function printCalendarItem($item)
    {
        $this->logWarning("########################## printCalendarItem ###############################################");
        //$this->logWarning(print_r($item,true));

        //$id = $item->ItemId->Id;
        $start = new DateTime($item->Start);
        $end = new DateTime($item->End);
        $recurrenceId = new DateTime($item->RecurrenceId);
        $output = 'Found event ' . $item->ItemId->Id . "\n" .
            '  Change Key: ' . $item->ItemId->ChangeKey . "\n" .
            '  CalendarItemType: ' . $item->CalendarItemType . "\n" .
            '  Title: ' . $item->Subject . "\n" .
            '  Start: ' . $start->format('l, F jS, Y g:ia') . "\n" .
            '  End:   ' . $end->format('l, F jS, Y g:ia') . "\n" .
            ////'  ParentFolderId ' . $item->ParentFolderId->Id . "\n" .
            ////'  ParentFolder Change Key: ' . $item->ParentFolderId->ChangeKey . "\n" .
            '  IsRecurring: ' . $item->IsRecurring . "\n" .
            ////'  Recurrence: ' . $item->Recurrence . "\n" .
            '  RecurrenceId: ' . $item->RecurrenceId . "\n" .
            '  $recurrenceId: ' . $recurrenceId->format('Y-m-d') . "\n\n";


        $this->logWarning($output);
    }

    protected function printCalendarItemFull($item)
    {
        $this->logWarning("########################## printCalendarItemFull ###############################################");
        $this->logWarning("CalendarItemType=[" . $item->CalendarItemType . "]");
        $this->logWarning("Start=[" . $item->Start . "]");
        $this->logWarning("End=[" . $item->End . "]");
        $this->logWarning("Subject=[" . $item->Subject . "]");
        $this->logWarning("UID=[" . $item->UID . "]");
        $this->logWarning("IsRecurring=[" . $item->IsRecurring . "]");
        //$this->logWarning("Recurrence=[" . $item->Recurrence . "]");
        $this->logWarning("RecurrenceId=[" . $item->RecurrenceId . "]");
        $this->logWarning("ItemClass=[" . $item->ItemClass . "]");
        $this->logWarning("ItemId->Id=[" . $item->ItemId->Id . "]");
        $this->logWarning("ItemId->ChangeKey=[" . $item->ItemId->ChangeKey . "]");


        $this->logWarning("  AdjacentMeetingCount=[" . $item->AdjacentMeetingCount . "]");
        $this->logWarning("  AdjacentMeetings=[" . $item->AdjacentMeetings . "]");
        $this->logWarning("  AllowNewTimeProposal=[" . $item->AllowNewTimeProposal . "]");
        $this->logWarning("  AppointmentReplyTime=[" . $item->AppointmentReplyTime . "]");
        $this->logWarning("  AppointmentSequenceNumber=[" . $item->AppointmentSequenceNumber . "]");
        $this->logWarning("  AppointmentState=[" . $item->AppointmentState . "]");

        $this->logWarning("  ConferenceType=[" . $item->ConferenceType . "]");
        $this->logWarning("  ConflictingMeetingCount=[" . $item->ConflictingMeetingCount . "]");
        $this->logWarning("  ConflictingMeetings=[" . $item->ConflictingMeetings . "]");
        $this->logWarning("  DateTimeStamp=[" . $item->DateTimeStamp . "]");
        $this->logWarning("  DeletedOccurrences=[" . $item->DeletedOccurrences . "]");
        $this->logWarning("  Duration=[" . $item->Duration . "]");

        $this->logWarning("  EndTimeZone=[" . $item->EndTimeZone . "]");
        $this->logWarning("  FirstOccurrence=[" . $item->FirstOccurrence . "]");
        $this->logWarning("  IsAllDayEvent=[" . $item->IsAllDayEvent . "]");
        $this->logWarning("  IsCancelled=[" . $item->IsCancelled . "]");
        $this->logWarning("  IsMeeting=[" . $item->IsMeeting . "]");
        $this->logWarning("  IsOnlineMeeting=[" . $item->IsOnlineMeeting . "]");

        $this->logWarning("  IsResponseRequested=[" . $item->IsResponseRequested . "]");
        $this->logWarning("  LastOccurrence=[" . $item->LastOccurrence . "]");
        $this->logWarning("  LegacyFreeBusyStatus=[" . $item->LegacyFreeBusyStatus . "]");
        $this->logWarning("  Location=[" . $item->Location . "]");
        $this->logWarning("  MeetingRequestWasSent=[" . $item->MeetingRequestWasSent . "]");
        $this->logWarning("  MeetingTimeZone=[" . $item->MeetingTimeZone . "]");
        $this->logWarning("  MeetingWorkspaceUrl=[" . $item->MeetingWorkspaceUrl . "]");
        $this->logWarning("  ModifiedOccurrences=[" . $item->ModifiedOccurrences . "]");
        $this->logWarning("  MyResponseType=[" . $item->MyResponseType . "]");
        $this->logWarning("  NetShowUrl=[" . $item->NetShowUrl . "]");
        $this->logWarning("  OptionalAttendees=[" . $item->OptionalAttendees . "]");
        ////$this->logWarning("  Organizer=[" . $item->Organizer . "]");
        $this->logWarning("  OriginalStart=[" . $item->OriginalStart . "]");

        $this->logWarning("  RequiredAttendees=[" . $item->RequiredAttendees . "]");
        $this->logWarning("  Resources=[" . $item->Resources . "]");

        $this->logWarning("  StartTimeZone=[" . $item->StartTimeZone . "]");
        $this->logWarning("  TimeZone=[" . $item->TimeZone . "]");

        $this->logWarning("  When=[" . $item->When . "]");
        $this->logWarning("  Attachments=[" . $item->Attachments . "]");
        $this->logWarning("  Body=[" . $item->Body . "]");
        $this->logWarning("  Culture=[" . $item->Culture . "]");
        $this->logWarning("  DateTimeCreated=[" . $item->DateTimeCreated . "]");
        $this->logWarning("  DateTimeReceived=[" . $item->DateTimeReceived . "]");
        $this->logWarning("  DateTimeSent=[" . $item->DateTimeSent . "]");
        $this->logWarning("  DisplayCc=[" . $item->DisplayCc . "]");
        $this->logWarning("  DisplayTo=[" . $item->DisplayTo . "]");
        $this->logWarning("  HasAttachments=[" . $item->HasAttachments . "]");
        $this->logWarning("  Importance=[" . $item->Importance . "]");
        $this->logWarning("  InReplyTo=[" . $item->InReplyTo . "]");
        $this->logWarning("  InternetMessageHeaders=[" . $item->InternetMessageHeaders . "]");
        $this->logWarning("  IsAssociated=[" . $item->IsAssociated . "]");
        $this->logWarning("  IsDraft=[" . $item->IsDraft . "]");
        $this->logWarning("  IsFromMe=[" . $item->IsFromMe . "]");
        $this->logWarning("  IsResend=[" . $item->IsResend . "]");
        $this->logWarning("  IsSubmitted=[" . $item->IsSubmitted . "]");
        $this->logWarning("  IsUnmodified=[" . $item->IsUnmodified . "]");

        $this->logWarning("  LastModifiedName=[" . $item->LastModifiedName . "]");
        $this->logWarning("  LastModifiedTime=[" . $item->LastModifiedTime . "]");
        $this->logWarning("  MimeContent=[" . $item->MimeContent . "]");
        $this->logWarning("  ParentFolderId->ChangeKey=[" . $item->ParentFolderId->ChangeKey . "]");
        $this->logWarning("  ParentFolderId->Id=[" . $item->ParentFolderId->Id . "]");
        $this->logWarning("  ReceivedBy=[" . $item->ReceivedBy . "]");
        $this->logWarning("  ReceivedRepresenting=[" . $item->ReceivedRepresenting . "]");
        $this->logWarning("  ReminderDueBy=[" . $item->ReminderDueBy . "]");
        $this->logWarning("  ReminderIsSet=[" . $item->ReminderIsSet . "]");
        $this->logWarning("  ReminderMinutesBeforeStart=[" . $item->ReminderMinutesBeforeStart . "]");
        $this->logWarning("  ResponseObjects=[" . $item->ResponseObjects . "]");
        $this->logWarning("  Sensitivity=[" . $item->Sensitivity . "]");
        $this->logWarning("  Size=[" . $item->Size . "]");
        $this->logWarning("  StoreEntryId=[" . $item->StoreEntryId . "]");

        $this->logWarning("  UniqueBody=[" . $item->UniqueBody . "]");
        $this->logWarning("  WebClientEditFormQueryString=[" . $item->WebClientEditFormQueryString . "]");
        $this->logWarning("  WebClientReadFormQueryString=[" . $item->WebClientReadFormQueryString . "]");
    }

    protected function logWarning($message)
    {
        if ($this->logger) {
            $this->logger->warning($message);
        }
    }
}
