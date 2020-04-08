<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AttributeGroupNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeGroupNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer, ObjectRepository $attributeRepository)
    {
        $this->beConstructedWith($stdNormalizer, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeGroupNormalizer::class);
    }

    function it_supports_an_attribute_group(AttributeGroupInterface $attributeGroup)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'internal_api')->shouldReturn(false);
        $this->supportsNormalization($attributeGroup, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($attributeGroup, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_an_attribute_group(
        $stdNormalizer,
        AttributeGroupInterface $attributeGroup,
        ObjectRepository $attributeRepository,
        AttributeInterface $sku,
        AttributeInterface $name
    ) {
        $sku->getCode()->willReturn('sku');
        $sku->getSortOrder()->willReturn(1);
        $name->getCode()->willReturn('name');
        $name->getSortOrder()->willReturn(2);
        $attributeGroup->getId()->willReturn(1);

        $data = ['code' => 'my_attribute_group', 'labels' => [], 'attributes' => ['sku', 'name']];

        $stdNormalizer->normalize($attributeGroup, 'standard', [])->willReturn($data);
        $attributeRepository->findBy(['code'=> ['sku', 'name']])->willReturn([$sku, $name]);

        $this->normalize($attributeGroup, 'internal_api', [])->shouldReturn(
            [
                'code'       => 'my_attribute_group',
                'labels'     => [
                ],
                'attributes' => ['sku', 'name'],
                'attributes_sort_order' => [
                    'sku' => 1,
                    'name' => 2
                ],
                'meta' => [
                    'id' => 1,
                    'attributeCount' => 2,
                    'totalAttributeCount' => 2,
                ]
            ]
        );
    }

    function it_normalizes_an_attribute_group_with_a_maximum_of_attributes(
        $stdNormalizer,
        AttributeGroupInterface $attributeGroup,
        ObjectRepository $attributeRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attribute');
        $attribute->getSortOrder()->willReturn(1);
        $attributeGroup->getId()->willReturn(1);

        $data = ['code' => 'my_attribute_group', 'labels' => [], 'attributes' => array_fill(0, 501, 'attribute')];

        $stdNormalizer->normalize($attributeGroup, 'standard', [])->willReturn($data);
        $attributeRepository->findBy(['code'=> array_fill(0, 500, 'attribute')])->willReturn(array_fill(0, 500, $attribute));

        $actual = $this->normalize($attributeGroup, 'internal_api', []);
        $actual['meta']['attribute_count']->shouldBe(500);
        $actual['meta']['total_attribute_count']->shouldBe(501);
        $actual['attributes']->shouldHaveCount(500);
    }
}