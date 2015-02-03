<?php

namespace Princeton\App\ExchangeAPI;

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
                $request = new \EWSType_CreateItemType();
                
                $request->Items = new \EWSType_NonEmptyArrayOfAllItemsType();
                $item = $request->Items->CalendarItem = new \EWSType_CalendarItemType();
                
                // Set the subject.
                $item->Subject = $eventDelegate->getSummary();
                
                // Set the start and end times. For Exchange 2007, you need to include the timezone offset.
                // For Exchange 2010, you should set the StartTimeZone and EndTimeZone properties. See below for
                // an example.
                $item->Start = $eventDelegate->getStartDateTime()->format(DATE_ISO8601);
                $item->End = $eventDelegate->getEndDateTime()->format(DATE_ISO8601);
                
                // Set no reminders
                // $item->ReminderIsSet = false;
                
                // Or use this to specify when reminder is displayed (if this is not set, the default is 15 minutes)
                $item->ReminderMinutesBeforeStart = $eventDelegate->getReminderMinutes();
                
                // Build the body.
                $item->Body = new \EWSType_BodyType();
                $item->Body->BodyType = \EWSType_BodyTypeType::HTML;
                $item->Body->_ = $eventDelegate->getDescription();
                
                // Set the item class type (not required).
                $item->ItemClass = new \EWSType_ItemClassType();
                $item->ItemClass->_ = \EWSType_ItemClassType::APPOINTMENT;
                
                // Set the sensitivity of the event (defaults to normal).
                $item->Sensitivity = new \EWSType_SensitivityChoicesType();
                $item->Sensitivity->_ = \EWSType_SensitivityChoicesType::NORMAL;
                
                // Add some categories to the event.
                $item->Categories = new \EWSType_ArrayOfStringsType();
                $item->Categories->String = array(
                    'Timeline'
                );
                
                // Set the importance of the event.
                $item->Importance = new \EWSType_ImportanceChoicesType();
                $item->Importance->_ = $eventDelegate->getImportance();
                
                // Point to the target shared calendar.
                $folder = new \EWSType_NonEmptyArrayOfBaseFolderIdsType();
                $folder->DistinguishedFolderId = new \EWSType_DistinguishedFolderIdType();
                $folder->DistinguishedFolderId->Id = \EWSType_DistinguishedFolderIdNameType::CALENDAR;
                $folder->DistinguishedFolderId->Mailbox = new \stdClass();
                $folder->DistinguishedFolderId->Mailbox->EmailAddress = $this->calDelegate->getCalendarId();
                //$request->ParentFolderIds = $folders;
                $request->SavedItemFolderId = $folder;
                
                // Don't send meeting invitations.
                $request->SendMeetingInvitations = \EWSType_CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;

                /* Now save the appointment into Exchange */
                /* @var $response \EWSType_CreateItemResponseType */
                $response = @$ews->CreateItem($request)
                    ->ResponseMessages
                    ->CreateItemResponseMessage;
                
                if (@$response->{'ResponseClass'} == 'Success') {
                    // Save the id and the change key
                    $itemId = @$response->{'Items'}->CalendarItem->ItemId;
                    $eventDelegate->setExchangeId(@$itemId->{'Id'});
                    $eventDelegate->setChangeKey(@$itemId->{'ChangeKey'});
                    $status = true;
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
                $request = new \EWSType_UpdateItemType();
                $request->ConflictResolution = 'AlwaysOverwrite';
                $request->SendMeetingInvitationsOrCancellations = 'SendOnlyToAll';
                $request->ItemChanges = array();
                
                $change = new \EWSType_ItemChangeType();
                $change->ItemId = new \EWSType_ItemIdType();
                $change->ItemId->Id = $eventDelegate->getExchangeId();
                $change->ItemId->ChangeKey = $eventDelegate->getChangeKey();
                $request->ItemChanges[] = $change;
                
                // Update Subject Property
                $field = new \EWSType_SetItemFieldType();
                $field->FieldURI = new \EWSType_PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = 'item:Subject';
                $field->CalendarItem = new \EWSType_CalendarItemType();
                $field->CalendarItem->Subject = $eventDelegate->getSummary();
                $change->Updates->SetItemField[] = $field;
                
                // Update Start Property
                $field = new \EWSType_SetItemFieldType();
                $field->FieldURI = new \EWSType_PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = 'calendar:Start';
                $field->CalendarItem = new \EWSType_CalendarItemType();
                $field->CalendarItem->Start = $eventDelegate->getStartDateTime()->format(DATE_ISO8601);
                $change->Updates->SetItemField[] = $field;
                
                // Update End Property
                $field = new \EWSType_SetItemFieldType();
                $field->FieldURI = new \EWSType_PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = 'calendar:End';
                $field->CalendarItem = new \EWSType_CalendarItemType();
                $field->CalendarItem->End = $eventDelegate->getEndDateTime()->format(DATE_ISO8601);
                $change->Updates->SetItemField[] = $field;
                
                // Update the body
                $field = new \EWSType_SetItemFieldType();
                $field->FieldURI = new \EWSType_PathToUnindexedFieldType();
                $field->FieldURI->FieldURI = 'item:Body';
                $field->CalendarItem = new \EWSType_CalendarItemType();
                $field->CalendarItem->Body = $eventDelegate->getDescription();
                $change->Updates->SetItemField[] = $field;
                
                // Make the change.
                $response = @$ews->UpdateItem($request)->{'ResponseMessages'}->UpdateItemResponseMessage;
                
                if (@$response->{'ResponseClass'} == 'Success') {
                    // Reset the change key
                    // $app->eid = $response->ResponseMessages->CreateItemResponseMessage->Items->CalendarItem->ItemId->Id;
                    $eventDelegate->setChangeKey(@$response->{'Items'}->CalendarItem->ItemId->ChangeKey);
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
                $request = new \EWSType_DeleteItemType();
                
                // Send to trash can, or use EWSType_DisposalType::HARD_DELETE instead to bypass the bin directly.
                $request->DeleteType = \EWSType_DisposalType::MOVE_TO_DELETED_ITEMS;
                // Inform no one who shares the item that it has been deleted.
                $request->SendMeetingCancellations = \EWSType_CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;
                
                // Set the item to be deleted.
                $item = new \EWSType_ItemIdType();
                $item->Id = $eventDelegate->getExchangeId();
                $item->ChangeKey = $eventDelegate->getChangeKey();
                
                // We can use this to mass delete but in this case it's just one item.
                $request->ItemIds = new \EWSType_NonEmptyArrayOfBaseItemIdsType();
                $request->ItemIds->ItemId = $item;
                
                // Send the delete request
                $response = $ews->DeleteItem($request)->{'ResponseMessages'}->UpdateItemResponseMessage;

                if (@$response->{'ResponseClass'} == 'Success') {
                    $status = true;
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
            $client = \EWSAutodiscover::getEWS($email, $password);
        }
        
        // If auto-discovery failed, try regular login.
        if (!$client) {
            $host = $this->calDelegate->getHostname();
            $username = $this->calDelegate->getUsername();
            
            if ($host && $username && $password) {
                $client = new \ExchangeWebServices($host, $username, $password);
            }
        }
        
        return $client;
    }
}
