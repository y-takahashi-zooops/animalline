<?php

namespace Customize\Tests\FormType\Admin;

use Customize\Entity\BreederExaminationInfo;
use Customize\Entity\Breeders;
use Customize\Form\Type\Admin\BreederExaminationInfoType;
use Customize\Form\Type\Admin\BreedersType;
use DateTime;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Master\Pref;
use Eccube\Form\Type\Master\PrefType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Validation;

class BreederExaminationInfoTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'breeder_name' => 'abc',
            'breeder_kana' => 'abc',
            'breeder_zip' => 'abc',
            'PrefBreeder' => new Pref(),
            'breeder_city' => 'abc',
            'breeder_address' => 'xyz',
            'breeder_tel' => '03497467',
            'breeder_fax' => '346770',
            'pr_text' => 'abc',
            'regal_effort' => 'abc',
            'license_name' => 'abc',
            'license_no' => 'abc',
            'license_zip' => 'abc',
            'PrefLicense' => new PrefType(),
            'license_city' => 'abc',
            'license_address' => 'abc',
            'license_house_name' => 'abc',
            'license_regist_date' => new DateTime(),
            'license_expire_date' => new DateTime(),
            'thumbnail_path' => new FileType(),
            'handling_pet_kind' => 1,
            'is_active' => 1,
            'examination_status' => 1,
            'breeder_house_name_dog' => 'abc',
            'breeder_house_name_cat' => 'abc',
            'DogHouseNameErrors' => 'abc',
            'CatHouseNameErrors' => 'abc'
        ];

        $form = $this->factory->create(BreedersType::class);
        $object = (new Breeders())
                ->setBreederRank(1);

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
