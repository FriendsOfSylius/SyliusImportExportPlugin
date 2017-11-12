<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Importer;

use Doctrine\Common\Persistence\ObjectManager;
use FriendsOfSylius\SyliusImportExportPlugin\Exception\ImporterException;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\ImporterInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\PaymentMethodsImporter;
use Payum\Core\Model\GatewayConfig;
use PhpSpec\ObjectBehavior;
use Port\Csv\CsvReader;
use Port\Csv\CsvReaderFactory;
use Prophecy\Argument;
use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class PaymentMethodsImporterSpec extends ObjectBehavior
{
    function let(
        CsvReaderFactory $csvReaderFactory,
        PaymentMethodFactoryInterface $paymentMethodFactory,
        RepositoryInterface $paymentMethodRepository,
        ObjectManager $paymentMethodsManager
    ) {
        $this->beConstructedWith($csvReaderFactory, $paymentMethodFactory, $paymentMethodRepository, $paymentMethodsManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PaymentMethodsImporter::class);
    }

    function it_implements_importer_interface()
    {
        $this->shouldImplement(ImporterInterface::class);
    }

    function it_imports_data_of_payment_methods_passed_in_csv_file(
        CsvReaderFactory $csvReaderFactory,
        CsvReader $csvReader,
        GatewayConfig $offlineGatewayConfig,
        GatewayConfig $paypalGatewayConfig,
        PaymentMethodFactoryInterface $paymentMethodFactory,
        RepositoryInterface $paymentMethodRepository,
        PaymentMethodInterface $offlinePaymentMethod,
        PaymentMethodInterface $paypalPaymentMethod
    ) {
        $csvReader->getColumnHeaders()->willReturn(['Code', 'Name', 'Instructions', 'Gateway']);
        $csvReader->key()->willReturn(0, 1);
        $csvReader->rewind()->willReturn();
        $csvReader->count()->willReturn(2);
        $csvReader->valid()->willReturn(true, true, false);
        $csvReader->next()->willReturn();
        $csvReader->current()->willReturn(
            ['Code' => 'OFFLINE', 'Name' => 'Offline', 'Instructions' => 'Offline payment method instructions.', 'Gateway' => 'offline'],
            ['Code' => 'PAYPAL', 'Name' => 'PayPal', 'Instructions' => 'PayPal payment method instructions.', 'Gateway' => 'paypal_express_checkout']
        );

        $csvReaderFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($csvReader);

        $paymentMethodRepository->findOneBy(['code' => 'PAYPAL'])->willReturn(null);
        $paymentMethodRepository->findOneBy(['code' => 'OFFLINE'])->willReturn(null);

        $paymentMethodFactory->createWithGateway('offline')->willReturn($offlinePaymentMethod);
        $paymentMethodFactory->createWithGateway('paypal_express_checkout')->willReturn($paypalPaymentMethod);

        $offlinePaymentMethod->setName('Offline')->shouldBeCalled();
        $offlinePaymentMethod->setCode('OFFLINE')->shouldBeCalled();
        $offlinePaymentMethod->setInstructions('Offline payment method instructions.')->shouldBeCalled();

        $offlinePaymentMethod->getGatewayConfig()->willReturn($offlineGatewayConfig);
        $offlineGatewayConfig->setGatewayName('Offline')->shouldBeCalled();
        $offlinePaymentMethod->setGatewayConfig($offlineGatewayConfig)->shouldBeCalled();

        $paypalPaymentMethod->setName('PayPal')->shouldBeCalled();
        $paypalPaymentMethod->setCode('PAYPAL')->shouldBeCalled();
        $paypalPaymentMethod->setInstructions('PayPal payment method instructions.')->shouldBeCalled();

        $paypalPaymentMethod->getGatewayConfig()->willReturn($paypalGatewayConfig);
        $paypalGatewayConfig->setGatewayName('PayPal')->shouldBeCalled();
        $paypalPaymentMethod->setGatewayConfig($paypalGatewayConfig)->shouldBeCalled();

        $this->import(__DIR__ . '/payment-methods.csv');
    }

    function it_updates_existing_payment_methods_data(
        CsvReaderFactory $csvReaderFactory,
        CsvReader $csvReader,
        GatewayConfig $offlineGatewayConfig,
        GatewayConfig $paypalGatewayConfig,
        PaymentMethodFactoryInterface $paymentMethodFactory,
        RepositoryInterface $paymentMethodRepository,
        PaymentMethodInterface $offlinePaymentMethod,
        PaymentMethodInterface $paypalPaymentMethod
    ) {
        $csvReader->getColumnHeaders()->willReturn(['Code', 'Name', 'Instructions', 'Gateway']);
        $csvReader->key()->willReturn(0, 1);
        $csvReader->rewind()->willReturn();
        $csvReader->count()->willReturn(2);
        $csvReader->valid()->willReturn(true, true, false);
        $csvReader->next()->willReturn();
        $csvReader->current()->willReturn(
            ['Code' => 'OFFLINE', 'Name' => 'Offline', 'Instructions' => 'Offline payment method instructions.', 'Gateway' => 'offline'],
            ['Code' => 'PAYPAL', 'Name' => 'PayPal', 'Instructions' => 'PayPal payment method instructions.', 'Gateway' => 'paypal_express_checkout']
        );

        $csvReaderFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($csvReader);

        $paymentMethodRepository->findOneBy(['code' => 'PAYPAL'])->willReturn($paypalPaymentMethod);
        $paymentMethodRepository->findOneBy(['code' => 'OFFLINE'])->willReturn(null);

        $paymentMethodFactory->createWithGateway('offline')->willReturn($offlinePaymentMethod);
        $paymentMethodFactory->createWithGateway('paypal_express_checkout')->willReturn($paypalPaymentMethod);

        $offlinePaymentMethod->setName('Offline')->shouldBeCalled();
        $offlinePaymentMethod->setCode('OFFLINE')->shouldBeCalled();
        $offlinePaymentMethod->setInstructions('Offline payment method instructions.')->shouldBeCalled();

        $offlinePaymentMethod->getGatewayConfig()->willReturn($offlineGatewayConfig);
        $offlineGatewayConfig->setGatewayName('Offline')->shouldBeCalled();
        $offlinePaymentMethod->setGatewayConfig($offlineGatewayConfig)->shouldBeCalled();

        $paypalPaymentMethod->setName('PayPal')->shouldBeCalled();
        $paypalPaymentMethod->setCode('PAYPAL')->shouldNotBeCalled();
        $paypalPaymentMethod->setInstructions('PayPal payment method instructions.')->shouldBeCalled();

        $paypalPaymentMethod->getGatewayConfig()->willReturn($paypalGatewayConfig);
        $paypalGatewayConfig->setGatewayName('PayPal')->shouldBeCalled();
        $paypalPaymentMethod->setGatewayConfig($paypalGatewayConfig)->shouldBeCalled();

        $this->import(__DIR__ . '/payment-methods.csv');
    }

    function it_fails_importing_data_of_payment_methods_for_missing_headers_in_csv_file(
        CsvReaderFactory $csvReaderFactory,
        CsvReader $csvReader
    ) {
        $csvReaderFactory->getReader(Argument::type(\SplFileObject::class))->willReturn($csvReader);

        $csvReader->getColumnHeaders()->willReturn([]);

        $this
            ->shouldThrow(ImporterException::class)
            ->during('import', [__DIR__ . '/payment-methods.csv'])
        ;
    }
}
