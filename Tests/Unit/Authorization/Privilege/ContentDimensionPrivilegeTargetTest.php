<?php
declare(strict_types=1);

namespace PunktDe\Form\Persistence\Tests\Unit\Authorization\Privilege;

/*
 *  (c) 2021 punkt.de GmbH - Karlsruhe, Germany - http://punkt.de
 *  All rights reserved.
 */

use Neos\Flow\Security\Authorization\Privilege\PrivilegeTarget;
use Neos\Flow\Tests\UnitTestCase;
use PunktDe\Form\Persistence\Authorization\Privilege\ContentDimensionPrivilege;
use PunktDe\Form\Persistence\Authorization\Privilege\ContentDimensionPrivilegeTarget;

class ContentDimensionPrivilegeTargetTest extends UnitTestCase
{

    public function contentDimensionPrivilegeMatcherDataProvider(): array
    {
        return [
            'onlyMatchingCountry' => [
                'matcher' => '{"country": ["fra"]}',
                'subject' => '{"country": {"0": "fra"},"language": {"0": "fr"}}',
                'match' => true,
            ],
            'notMatchingCountry' => [
                'matcher' => '{"country": ["deu"]}',
                'subject' => '{"country": {"0": "fra"},"language": {"0": "fr"}}',
                'match' => false,
            ],
            'matchLanguageAndCountry' => [
                'matcher' => '{"country": ["fra"], "language": ["fr"]}',
                'subject' => '{"country": {"0": "fra"},"language": {"0": "fr"}}',
                'match' => true,
            ],
            'matchMultipleCountry' => [
                'matcher' => '{"country": ["fra", "deu"], "language": ["fr"]}',
                'subject' => '{"country": {"0": "fra"},"language": {"0": "fr"}}',
                'match' => true,
            ],
            'matchCountryWrongLanguage' => [
                'matcher' => '{"country": ["fra", "deu"], "language": ["en"]}',
                'subject' => '{"country": {"0": "fra"},"language": {"0": "fr"}}',
                'match' => false,
            ],
            'starOnly' => [
                'matcher' => '*',
                'subject' => '{"country": {"0": "fra"},"language": {"0": "fr"}}',
                'match' => true,
            ],
            'starMatchingCountry' => [
                'matcher' => '{"country": "*", "language": ["fr"]}',
                'subject' => '{"country": {"0": "fra"},"language": {"0": "fr"}}',
                'match' => true,
            ],
            'starMatchingCountryWrongLanguage' => [
                'matcher' => '{"country": "*", "language": ["en"]}',
                'subject' => '{"country": {"0": "fra"},"language": {"0": "fr"}}',
                'match' => false,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider contentDimensionPrivilegeMatcherDataProvider
     *
     * @param string $matcher
     * @param string $subject
     * @param bool $expected
     * @throws \JsonException
     * @throws \Neos\Flow\Security\Exception\InvalidPrivilegeTypeException
     */
    public function contentDimensionPrivilegeMatcherTest(string $matcher, string $subject, bool $expected): void
    {
        $privilegeTargetMock = $this->getMockBuilder(PrivilegeTarget::class)->disableOriginalConstructor()->getMock();
        $contentDimensionPrivilegeTarget = new ContentDimensionPrivilegeTarget(json_decode($subject, true, 512, JSON_THROW_ON_ERROR));
        $contentDimensionPrivilegeMock = new ContentDimensionPrivilege($privilegeTargetMock, $matcher, 'GRANT', []);

        self::assertEquals($expected, $contentDimensionPrivilegeMock->matchesSubject($contentDimensionPrivilegeTarget));
    }

}
