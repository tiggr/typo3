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

namespace TYPO3\CMS\Core\Tests\Functional\DataScenarios\Select\WorkspacesModify;

use TYPO3\CMS\Core\Tests\Functional\DataScenarios\Select\AbstractActionWorkspacesTestCase;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\DoesNotHaveRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\HasRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\StructureDoesNotHaveRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Constraint\RequestSection\StructureHasRecordConstraint;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\ResponseContent;

final class ActionTest extends AbstractActionWorkspacesTestCase
{
    /**
     * @test
     */
    public function verifyCleanReferenceIndex(): void
    {
        // The test verifies the imported data set has a clean reference index by the check in tearDown()
        self::assertTrue(true);
    }

    /**
     * @test
     */
    public function addElementRelation(): void
    {
        parent::addElementRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/addElementRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #1', 'Element #2', 'Element #3'));
    }

    /**
     * @test
     */
    public function deleteElementRelation(): void
    {
        parent::deleteElementRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/deleteElementRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #1'));
        self::assertThat($responseSections, (new StructureDoesNotHaveRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #2', 'Element #3'));
    }

    /**
     * @test
     */
    public function changeElementSorting(): void
    {
        parent::changeElementSorting();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/changeElementSorting.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #1', 'Element #2'));
    }

    /**
     * @test
     */
    public function changeElementRelationSorting(): void
    {
        parent::changeElementRelationSorting();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/changeElementRelationSorting.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #1', 'Element #2'));
    }

    /**
     * @test
     */
    public function createContentAndAddElementRelation(): void
    {
        parent::createContentAndAddElementRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createContentNAddRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #1'));
    }

    /**
     * @test
     */
    public function createContentAndCreateElementRelation(): void
    {
        parent::createContentAndCreateElementRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/createContentNCreateRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['newContentId'])->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Testing #1'));
    }

    /**
     * @test
     */
    public function modifyElementOfRelation(): void
    {
        parent::modifyElementOfRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyElementOfRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Testing #1', 'Element #2'));
    }

    /**
     * @test
     */
    public function modifyContentOfRelation(): void
    {
        parent::modifyContentOfRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyContentOfRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
    }

    /**
     * @test
     */
    public function modifyBothSidesOfRelation(): void
    {
        parent::modifyBothSidesOfRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/modifyBothSidesOfRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Testing #1', 'Element #2'));
        self::assertThat($responseSections, (new HasRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
    }

    /**
     * @test
     */
    public function deleteContentOfRelation(): void
    {
        parent::deleteContentOfRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/deleteContentOfRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new DoesNotHaveRecordConstraint())
            ->setTable(self::TABLE_Content)->setField('header')->setValues('Testing #1'));
    }

    /**
     * @test
     */
    public function deleteElementOfRelation(): void
    {
        parent::deleteElementOfRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/deleteElementOfRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureDoesNotHaveRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #1'));
    }

    /**
     * @test
     */
    public function copyContentOfRelation(): void
    {
        parent::copyContentOfRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/copyContentOfRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        // Referenced elements are not copied with the "parent", which is expected and correct
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . $this->recordIds['copiedContentId'])->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #2', 'Element #3'));
    }

    /**
     * @test
     */
    public function copyElementOfRelation(): void
    {
        parent::copyElementOfRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/copyElementOfRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #1'));
        // Referenced elements are not updated at the "parent", which is expected and correct
        self::assertThat($responseSections, (new StructureDoesNotHaveRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #1 (copy 1)'));
    }

    /**
     * @test
     */
    public function localizeContentOfRelation(): void
    {
        parent::localizeContentOfRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/localizeContentOfRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #2', 'Element #3'));
    }

    /**
     * @test
     */
    public function localizeElementOfRelation(): void
    {
        // Create translated page first
        $this->actionService->copyRecordToLanguage('pages', self::VALUE_PageId, self::VALUE_LanguageId);
        parent::localizeElementOfRelation();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/localizeElementOfRelation.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageId)->withLanguageId(self::VALUE_LanguageId),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdFirst)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('[Translate to Dansk:] Element #1', 'Element #2'));
    }

    /**
     * @test
     */
    public function moveContentOfRelationToDifferentPage(): void
    {
        parent::moveContentOfRelationToDifferentPage();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/moveContentOfRelationToDifferentPage.csv');

        $response = $this->executeFrontendSubRequest(
            (new InternalRequest())->withPageId(self::VALUE_PageIdTarget),
            (new InternalRequestContext())->withBackendUserId(self::VALUE_BackendUserId)->withWorkspaceId(self::VALUE_WorkspaceId)
        );
        $responseSections = ResponseContent::fromString((string)$response->getBody())->getSections();
        self::assertThat($responseSections, (new StructureHasRecordConstraint())
            ->setRecordIdentifier(self::TABLE_Content . ':' . self::VALUE_ContentIdLast)->setRecordField(self::FIELD_ContentElement)
            ->setTable(self::TABLE_Element)->setField('title')->setValues('Element #2', 'Element #3'));
    }

    /**
     * @test
     */
    public function localizeContentOfRelationWithLocalizeReferencesAtParentLocalization()
    {
        parent::localizeContentOfRelationWithLocalizeReferencesAtParentLocalization();
        $this->assertCSVDataSet(__DIR__ . '/DataSet/localizeContentOfRelationWLocalizeReferencesAtParentLocalization.csv');
    }
}
