<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Core\Tests\Acceptance\Application\Scheduler;

use TYPO3\CMS\Core\Tests\Acceptance\Support\ApplicationTester;
use TYPO3\CMS\Core\Tests\Acceptance\Support\Helper\ModalDialog;

/**
 * Scheduler task tests
 */
final class TasksCest
{
    public function _before(ApplicationTester $I): void
    {
        $I->useExistingSession('admin');
        $I->scrollTo('[data-modulemenu-identifier="scheduler"]');
        $I->see('Scheduler', '[data-modulemenu-identifier="scheduler"]');
        $I->click('[data-modulemenu-identifier="scheduler"]');
        $I->switchToContentFrame();
    }

    public function createASchedulerTask(ApplicationTester $I): void
    {
        $I->see('No tasks defined yet');
        $I->click('//a[contains(@title, "Add new scheduler task")]', '.module-docheader');
        $I->waitForElementNotVisible('#task_SystemStatusUpdateNotificationEmail');

        $I->amGoingTo('check save action in case no settings given');
        $I->click('button[value="saveclose"]');
        $I->waitForText('No frequency was defined, either as an interval or as a cron command.');

        $I->selectOption('#task_class', 'System Status Update');
        $I->seeElement('#task_SystemStatusUpdateNotificationEmail');
        $I->selectOption('#task_type', 'Single');
        $I->fillField('#task_SystemStatusUpdateNotificationEmail', 'test@local.typo3.org');
        $I->wantTo('Click "Save and close"');
        $I->click('button[value="saveclose"]');
        $I->waitForText('The task was added successfully.');
    }

    /**
     * @depends createASchedulerTask
     */
    public function canRunTask(ApplicationTester $I): void
    {
        // run the task
        $I->click('button[name="execute"]');
        $I->waitForText('Task "System Status Update (reports)" with uid "1" has been executed.');
        $I->seeElement('[data-module-name="scheduler_manage"] .disabled');
        $I->see('disabled');
    }

    /**
     * @depends createASchedulerTask
     */
    public function canEditTask(ApplicationTester $I): void
    {
        $I->click('//a[contains(@title, "Edit")]');
        $I->waitForText('Edit scheduled task "System Status Update (reports)"');
        $I->seeInField('#task_SystemStatusUpdateNotificationEmail', 'test@local.typo3.org');
        $I->fillField('#task_SystemStatusUpdateNotificationEmail', 'foo@local.typo3.org');
        $I->wantTo('Click "Save and close"');
        $I->click('button[value="saveclose"]');
        $I->waitForText('The task was updated successfully.');
    }

    /**
     * @depends canRunTask
     */
    public function canEnableAndDisableTask(ApplicationTester $I): void
    {
        $I->wantTo('See a enable button for a task');
        $I->click('//button[contains(@title, "Enable")]', '#tx_scheduler_form');
        $I->dontSeeElement('[data-module-name="scheduler_manage"] .disabled');
        $I->dontSee('disabled');
        $I->wantTo('See a disable button for a task');
        // Give tooltips some time to fully init
        $I->wait(1);
        $I->moveMouseOver('//button[contains(@title, "Disable")]');
        $I->wait(1);
        $I->click('//button[contains(@title, "Disable")]');
        $I->waitForElementVisible('[data-module-name="scheduler_manage"]');
        $I->seeElement('[data-module-name="scheduler_manage"] .disabled');
        $I->see('disabled');
    }

    /**
     * @depends createASchedulerTask
     */
    public function canDeleteTask(ApplicationTester $I, ModalDialog $modalDialog): void
    {
        $I->wantTo('See a delete button for a task');
        $I->seeElement('//a[contains(@title, "Delete")]');
        $I->click('//a[contains(@title, "Delete")]');
        $I->wantTo('Cancel the delete dialog');

        // don't use $modalDialog->clickButtonInDialog due to too low timeout
        $modalDialog->canSeeDialog();
        $I->click('Cancel', ModalDialog::$openedModalButtonContainerSelector);
        $I->waitForElementNotVisible(ModalDialog::$openedModalSelector, 30);

        $I->switchToContentFrame();
        $I->wantTo('Still see and can click the Delete button as the deletion has been canceled');
        $I->click('//a[contains(@title, "Delete")]');
        $modalDialog->clickButtonInDialog('OK');
        $I->switchToContentFrame();
        $I->see('The task was successfully deleted.');
        $I->see('No tasks defined yet');
    }

    public function canSwitchToSetupCheck(ApplicationTester $I): void
    {
        $I->selectOption('select[name=moduleMenu]', 'Scheduler setup check');
        $I->waitForElementVisible('[data-module-name="scheduler_setupcheck"]');
        $I->see('Scheduler setup check');
        $I->see('This screen checks if the requisites for running the Scheduler as a cron job are fulfilled');
    }

    public function canSwitchToInformation(ApplicationTester $I): void
    {
        $I->selectOption('select[name=moduleMenu]', 'Available scheduler tasks');
        $I->waitForElementVisible('[data-module-name="scheduler_availabletasks"]');
        $I->see('Available scheduler tasks');
        $I->canSeeNumberOfElements('[data-module-name="scheduler_availabletasks"] table tbody tr', [1, 10000]);
    }

    public function canCreateNewTaskGroupFromEditForm(ApplicationTester $I): void
    {
        $I->amGoingTo('create a task when none exists yet');
        $I->canSee('Scheduled tasks', 'h1');
        $this->createASchedulerTask($I);

        $I->amGoingTo('test the new task group button on task edit view');
        $I->click('[data-scheduler-table] > tbody > tr > td.nowrap > div:nth-child(1) > a:nth-child(1)');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->canSee('Edit scheduled task "System Status Update (reports)"');
        $I->click('#task_group_row > div > div > div > div > a');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->canSee('Create new Scheduler task group on root level', 'h1');

        $I->fillField('//input[contains(@data-formengine-input-name, "data[tx_scheduler_task_group]") and contains(@data-formengine-input-name, "[groupName]")]', 'new task group');
        $I->click('button[name="_savedok"]');
        $I->wait(0.2);
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('a[title="Close"]');
        $I->waitForElementVisible('#tx_scheduler_form');

        $I->selectOption('select#task_group', 'new task group');
        $I->click('button[value="save"]');
        $I->waitForElementNotVisible('#t3js-ui-block');
        $I->click('a[title="Close"]');
        $I->waitForElementVisible('[data-module-name="scheduler_manage"]');

        $I->canSee('new task group', '.panel-heading');
    }
}
