<?php

declare(strict_types=1);

namespace App\Tests\unit\App\Service;

use App\Contract\ApiResponseInterface;
use App\Dto\CommissionInfoDto;
use App\Service\CommissionService;
use App\Service\MiscService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

class CommissionServiceTest extends MockeryTestCase
{
    private MockInterface|ApiResponseInterface $apiResponse;
    private MockInterface|MiscService $miscService;
    private CommissionService $commissionService;

    public function setUp(): void
    {
        $this->apiResponse = \Mockery::mock(ApiResponseInterface::class);
        $this->miscService = \Mockery::mock(MiscService::class);
        $this->commissionService = new CommissionService($this->apiResponse, $this->miscService, ['RO', 'SS', 'VV']);
    }

    public function testGetCommissionValueSuccess(): void
    {
        $dto = new CommissionInfoDto('123456', '12.00', 'RO');
        $this->apiResponse->expects()->getResponse($dto)->andReturnSelf();
        $this->apiResponse->expects()->getCountryCode()->andReturn('RO');
        $this->apiResponse->expects()->getRateValue()->twice()->andReturn(10);
        $this->miscService->expects()->getRoundedNumberUp(1.2)->andReturn(30.00);

        $actual = $this->commissionService->getCommissionValue($dto);
        $this->assertEquals(30 * 0.01, $actual);
    }

    public function testGetCommissionZeroRateValue(): void
    {
        $dto = new CommissionInfoDto('123457', '13.00', 'EUR');
        $this->apiResponse->expects()->getResponse($dto)->andReturnSelf();
        $this->apiResponse->expects()->getCountryCode()->andReturn('RO');
        $this->apiResponse->expects()->getRateValue()->andReturn(0);
        $this->miscService->expects()->getRoundedNumberUp(13)->andReturn(40.00);

        $actual = $this->commissionService->getCommissionValue($dto);
        $this->assertEquals(40 * 0.01, $actual);
    }

    public function testWithEuroAsCurrencyAndPositiveRateValue(): void
    {
        $dto = new CommissionInfoDto('123457', '14.00', 'EUR');
        $this->apiResponse->expects()->getResponse($dto)->andReturnSelf();
        $this->apiResponse->expects()->getCountryCode()->andReturn('RO');
        $this->apiResponse->expects()->getRateValue()->twice()->andReturn(10);
        $this->miscService->expects()->getRoundedNumberUp(14)->andReturn(50.00);
        $this->miscService->expects()->getRoundedNumberUp(1.4)->andReturn(50.00);

        $actual = $this->commissionService->getCommissionValue($dto);
        $this->assertEquals(50 * 0.01, $actual);
    }

    public function testWithOtherCurrencyAndNegativeRateValue(): void
    {
        $dto = new CommissionInfoDto('123457', '15.00', 'WW');
        $this->apiResponse->expects()->getResponse($dto)->andReturnSelf();
        $this->apiResponse->expects()->getCountryCode()->andReturn('RO');
        $this->apiResponse->expects()->getRateValue()->twice()->andReturn(-10);
        $this->miscService->expects()->getRoundedNumberUp(-1.5)->andReturn(-15);

        $actual = $this->commissionService->getCommissionValue($dto);
        $this->assertEquals(-15 * 0.01, $actual);
    }

    public function testOtherCountryCode(): void
    {
        $dto = new CommissionInfoDto('123457', '16.00', 'TT');
        $this->apiResponse->expects()->getResponse($dto)->andReturnSelf();
        $this->apiResponse->expects()->getCountryCode()->andReturn('ZZ');
        $this->apiResponse->expects()->getRateValue()->twice()->andReturn(-10);
        $this->miscService->expects()->getRoundedNumberUp(-1.6)->andReturn(-15);

        $actual = $this->commissionService->getCommissionValue($dto);
        $this->assertEquals(-15 * 0.02, $actual);
    }
}
