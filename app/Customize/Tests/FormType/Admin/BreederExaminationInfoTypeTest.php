<?php

namespace Customize\Tests\FormType\Admin;

use Customize\Entity\BreederExaminationInfo;
use Customize\Form\Type\Admin\BreederExaminationInfoType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class BreederExaminationInfoTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'pedigree_organization_other' => 'abc',
            'pedigree_organization' => 1,
            'parent_pet_count_1' => 1,
            'parent_pet_count_2' => 1,
            'parent_pet_count_3' => 1,
            'parent_pet_buy_place_1' => 1,
            'parent_pet_buy_place_2' => 1,
            'parent_pet_buy_place_3' => 1,
            'owner_worktime_ave' => 1,
            'family_staff_count' => 1,
            'family_staff_worktime_ave' => 1,
            'fulltime_staff_worktime_ave' => 1,
            'parttime_staff_count' => 1,
            'parttime_staff_worktime_ave' => 1,
            'other_staff_count' => 1,
            'other_staff_worktime_ave' => 1,
            'breeding_exp_year' => 1,
            'breeding_exp_month' => 1,
            'is_participate_show' => 1,
            'cage_size_1' => 0,
            'cage_size_2' => 0,
            'cage_size_3' => 0,
            'cage_size_other' => 'abc',
            'exercise_status' => 1,
            'exercise_status_other' => 'abc',
            'publish_pet_count' => 1,
            'breeding_experience' => 1,
            'selling_experience' => 1
        ];

        $object = new BreederExaminationInfo();

        $form = $this->factory->create(BreederExaminationInfoType::class, $object);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    protected function getExtensions(): array
    {
        return [new ValidatorExtension(Validation::createValidator())];
    }
}
