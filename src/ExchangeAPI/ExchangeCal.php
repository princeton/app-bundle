<?php

namespace Princeton\App\ExchangeAPI;

use PhpEws\AutodiscoveryManager;
use PhpEws\EwsConnection;
use PhpEws\DataType\ArrayOfStringsType;
use PhpEws\DataType\BodyType;
use PhpEws\DataType\BodyTypeType;
use PhpEws\DataType\CalendarItemCreateOrDeleteOperationType;
use PhpEws\DataType\CalendarItemType;
use PhpEws\DataType\CreateItemResponseType;
use PhpEws\DataType\CreateItemType;
use PhpEws\DataType\DeleteItemType;
use PhpEws\DataType\DisposalType;
use PhpEws\DataType\DistinguishedFolderIdNameType;
use PhpEws\DataType\DistinguishedFolderIdType;
use PhpEws\DataType\ImportanceChoicesType;
use PhpEws\DataType\ItemChangeType;
use PhpEws\DataType\ItemClassType;
use PhpEws\DataType\ItemIdType;
use PhpEws\DataType\NonEmptyArrayOfAllItemsType;
use PhpEws\DataType\NonEmptyArrayOfBaseFolderIdsType;
use PhpEws\DataType\NonEmptyArrayOfBaseItemIdsType;
use PhpEws\DataType\PathToUnindexedFieldType;
use PhpEws\DataType\SensitivityChoicesType;
use PhpEws\DataType\SetItemFieldType;
use PhpEws\DataType\UpdateItemType;

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
    /**
     * @var ExchangeCalDelegate
     */
    private $calDelegate;
    
    /**
     * @param ExchangeCalDelegate $calDelegate
     */
    public function __construct(ExchangeCalDelegate $calDelegate)
    {
        $this->calDelegate = $calDelegate;
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
                $this->calDelegate->logWarning(
                    "Exchange sync error: not configured for insert of item "
                    . $eventDelegate->getId());
            } elseif (!$ews) {
                $this->calDelegate->logWarning(
                    "Exchange sync error:  unable to create service for insert of item "
                    . $eventDelegate->getId());
            } else {
                // Start building the request.
                $request = new CreateItemType();
                
                $request->Items = new NonEmptyArrayOfAllItemsType();
                $item = $request->Items->CalendarItem = new CalendarItemType();
                
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
                
                // TODO Fix recurrence exceptions in Exchange??
                // http://stackoverflow.com/questions/23815461/creating-a-recurring-calendar-event-with-php-ews
                $rfc2445 = @$eventDelegate->{'getRfc2445'}();
                if ($rfc2445) {
                	$item->MimeContent = base64_encode($rfc2445);
                }
                
                // Point to the target shared calendar.
                $folder = new NonEmptyArrayOfBaseFolderIdsType();
                $folder->DistinguishedFolderId = new DistinguishedFolderIdType();
                $folder->DistinguishedFolderId->Id = DistinguishedFolderIdNameType::CALENDAR;
                $folder->DistinguishedFolderId->Mailbox = new \stdClass();
                $folder->DistinguishedFolderId->Mailbox->EmailAddress = $this->calDelegate->getCalendarMailbox();
                $request->SavedItemFolderId = $folder;
                
                // Don't send meeting invitations.
                $request->SendMeetingInvitations = CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;

                /* Now save the appointment into Exchange */
                /* @var $response CreateItemResponseType */
                $response = @$ews->CreateItem($request)
                    ->ResponseMessages
                    ->CreateItemResponseMessage;
                
                if (@$response->{'ResponseClass'} == 'Success') {
                    // Save the id and the change key
                    $itemId = @$response->{'Items'}->CalendarItem->ItemId;
                    $eventDelegate->setEwsId(@$itemId->{'Id'});
                    $eventDelegate->setEwsChangeKey(@$itemId->{'ChangeKey'});
                    $status = true;
                } else {
                	$this->calDelegate->logWarning(print_r($response, true));
                }
            }
        } catch (\Exception $ex) {
            $this->calDelegate->logWarning(
                "Exchange sync error inserting event for item "
                . $eventDelegate->getId()
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
        $status = false;
        try {
            $ews = $this->buildClient();
            if (!$this->isConfigured()) {
                $this->calDelegate->logWarning(
                    "Exchange sync error: not configured for update of item "
                    . $eventDelegate->getId());
            } elseif (!$ews) {
                $this->calDelegate->logWarning(
                    "Exchange sync error:  unable to create service for update of item "
                    . $eventDelegate->getId());
            } else {
                // Start building the request
                $request = new UpdateItemType();
                $request->ConflictResolution = 'AlwaysOverwrite';
                $request->SendMeetingInvitationsOrCancellations = 'SendOnlyToAll';
                $request->ItemChanges = array();
                
                $change = new ItemChangeType();
                $change->ItemId = new ItemIdType();
                $change->ItemId->Id = $eventDelegate->getEwsId();
                $change->ItemId->ChangeKey = $eventDelegate->getEwsChangeKey();
                $request->ItemChanges[] = $change;
                
                // Update Subject Property
                $field = new SetItemFieldType();
                $field->FieldURI = new PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = 'item:Subject';
                $field->CalendarItem = new CalendarItemType();
                $field->CalendarItem->Subject = $eventDelegate->getSummary();
                $change->Updates->SetItemField[] = $field;
                
                // Update Start Property
                $field = new SetItemFieldType();
                $field->FieldURI = new PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = 'calendar:Start';
                $field->CalendarItem = new CalendarItemType();
                $field->CalendarItem->Start = $eventDelegate->getStartDateTime()->format(\DateTime::W3C);
                $change->Updates->SetItemField[] = $field;
                
                // Update End Property
                $field = new SetItemFieldType();
                $field->FieldURI = new PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = 'calendar:End';
                $field->CalendarItem = new CalendarItemType();
                $field->CalendarItem->End = $eventDelegate->getEndDateTime()->format(\DateTime::W3C);
                $change->Updates->SetItemField[] = $field;
                
                // TODO Implement recurrence for Exchange events.
                
                // Update the body
                $field = new SetItemFieldType();
                $field->FieldURI = new PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = 'item:Body';
                $field->CalendarItem = new CalendarItemType();
                $field->CalendarItem->Body = $eventDelegate->getDescription();
                $change->Updates->SetItemField[] = $field;
                
                // Make the change.
                $response = @$ews->UpdateItem($request)->{'ResponseMessages'}->UpdateItemResponseMessage;
                
                if (@$response->{'ResponseClass'} == 'Success') {
                    // Reset the change key
                    // $app->eid = $response->ResponseMessages->CreateItemResponseMessage->Items->CalendarItem->ItemId->Id;
                    $eventDelegate->setEwsChangeKey(@$response->{'Items'}->CalendarItem->ItemId->ChangeKey);
                    $status = true;
                }
            }
        } catch (\Exception $ex) {
            $this->calDelegate->logWarning(
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
        $status = false;
        try {
            $ews = $this->buildClient();
            if (!$this->isConfigured()) {
                $this->calDelegate->logWarning(
                    "Exchange sync error: not configured for delete of item "
                    . $eventDelegate->getId());
            } elseif (!$ews) {
                $this->calDelegate->logWarning(
                    "Exchange sync error:  unable to create service for delete of item "
                    . $eventDelegate->getId());
            } else {
                $request = new DeleteItemType();
                
                // Send to trash can, or useDisposalType::HARD_DELETE instead to bypass the bin directly.
                $request->DeleteType = DisposalType::MOVE_TO_DELETED_ITEMS;
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
                $response = $ews->DeleteItem($request)->{'ResponseMessages'}->UpdateItemResponseMessage;

                if (@$response->{'ResponseClass'} == 'Success') {
                    $status = true;
                    $eventDelegate->setEwsId(null);
                    $eventDelegate->setEwsChangeKey(null);
                }
            }
        } catch (\Exception $ex) {
            $this->calDelegate->logWarning(
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
        
        if ($email && $password) {
            // Try auto-discovery
            $client = AutodiscoveryManager::getConnection($email, $password);
        }
        
        // If auto-discovery failed, try regular login.
        if (!$client) {
            $host = $this->calDelegate->getHostname();
            $username = $this->calDelegate->getUsername();
            
            if ($host && $username && $password) {
                $client = new EwsConnection($host, $username, $password);
            }
        }
        
        return $client;
    }
}
