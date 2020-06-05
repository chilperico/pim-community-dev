<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Service\CollectApiError;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\MonitoredRoutes;
use Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber\ApiErrorEventSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Event\ProductValidationErrorEvent;
use Akeneo\Pim\Enrichment\Bundle\Event\TechnicalErrorEvent;
use Akeneo\Pim\Enrichment\Component\Error\IdentifiableDomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolationList;

class ApiErrorEventSubscriberSpec extends ObjectBehavior
{
    public function let(CollectApiError $collectApiError): void
    {
        $this->beConstructedWith($collectApiError);
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDomainErrorEvent::class => 'collectProductDomainError',
            ProductValidationErrorEvent::class => 'collectProductValidationError',
            TechnicalErrorEvent::class => 'collectTechnicalError',
            KernelEvents::TERMINATE => 'flushApiErrors',
        ]);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldHaveType(ApiErrorEventSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }


    public function it_collects_a_product_domain_error($collectApiError): void
    {
        $error = UnknownAttributeException::unknownAttribute('attribute_code');
        $product = new Product();
        $event = new ProductDomainErrorEvent($error, $product);

        $collectApiError->collectFromProductDomainError($product, $error)->shouldBeCalled();

        $this->collectProductDomainError($event);
    }

    public function it_collects_a_product_validation_error($collectApiError): void
    {
        $constraintViolationList = new ConstraintViolationList();
        $product = new Product();
        $event = new ProductValidationErrorEvent($constraintViolationList, $product);

        $collectApiError->collectFromProductValidationError($product, $constraintViolationList)->shouldBeCalled();

        $this->collectProductValidationError($event);
    }

    public function it_collects_a_technical_error($collectApiError): void
    {
        $error = new \Exception();
        $event = new TechnicalErrorEvent($error);

        $collectApiError->collectFromTechnicalError($error)->shouldBeCalled();

        $this->collectTechnicalError($event);
    }

    public function it_flushes_collected_errors($collectApiError, TerminateEvent $terminateEvent): void
    {
        $collectApiError->flush()->shouldBeCalled();

        $this->flushApiErrors($terminateEvent);
    }
}