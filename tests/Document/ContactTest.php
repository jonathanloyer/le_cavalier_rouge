<?php

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Document\Contact;

class ContactTest extends KernelTestCase
{
    private DocumentManager $dm;

    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    protected function setUp(): void
    {
        self::bootKernel();

        if (method_exists(self::class, 'getContainer')) {
            $this->dm = self::getContainer()->get(DocumentManager::class);
        } else {
            $this->dm = self::$kernel->getContainer()->get(DocumentManager::class);
        }

        // Forcer l'utilisation d'une base de test temporaire
        $this->dm->getConfiguration()->setDefaultDB('le_cavalier_rouge_test');
    }

    public function testCreateContact()
    {
        // Création d'un contact de test
        $contact = new Contact();
        $contact->setName("John")
                ->setFirstname("Doe")
                ->setEmail("john.doe@example.com")
                ->setMessage("Ceci est un test")
                ->setCreatedAt(new \DateTime());

        // Persistance dans MongoDB
        $this->dm->persist($contact);
        $this->dm->flush();

        // Vérification de l'insertion en base
        $savedContact = $this->dm->getRepository(Contact::class)->findOneBy(['email' => 'john.doe@example.com']);

        $this->assertNotNull($savedContact, "Le contact n'a pas été enregistré !");
        $this->assertEquals("John", $savedContact->getName());
        $this->assertEquals("Doe", $savedContact->getFirstname());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Nettoyer la base après le test
        $this->dm->getDocumentCollection(Contact::class)->drop();
    }
}
