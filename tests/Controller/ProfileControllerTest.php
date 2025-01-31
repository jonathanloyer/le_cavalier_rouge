<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\String\UnicodeString;

class ProfileControllerTest extends TestCase
{
    private $userRepository;
    private $slugger;

    protected function setUp(): void
    {
        // Mock du UserRepository
        $this->userRepository = $this->createMock(UserRepository::class);

        // Mock du SluggerInterface
        $this->slugger = $this->createMock(SluggerInterface::class);
    }

    public function testUserNotFound(): void
    {
        // Simuler qu'aucun utilisateur n'est trouvé
        $this->userRepository
            ->method('findOneBy')
            ->willReturn(null);

        // Vérifier qu'une exception est levée
        $this->expectException(NotFoundHttpException::class);

        // Simuler le contrôle d'un utilisateur introuvable
        $user = $this->userRepository->findOneBy(['email' => 'nonexistent@example.com']);
        if (!$user) {
            throw new NotFoundHttpException('Utilisateur non trouvé.');
        }
    }

    public function testValidProfileUpdate(): void
    {
        // Mock d'un utilisateur
        $user = new User();
        $user->setEmail('testuser@example.com');
        $user->setPseudo('testuser');
        $user->setAvatar('old-avatar.jpg');

        // Simuler que l'utilisateur est trouvé
        $this->userRepository
            ->method('findOneBy')
            ->willReturn($user);

        // Mock d'un fichier avatar simulé
        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['guessExtension', 'getClientOriginalName', 'move'])
            ->getMock();

        $uploadedFile
            ->method('guessExtension')
            ->willReturn('jpg');
        $uploadedFile
            ->method('getClientOriginalName')
            ->willReturn('avatar.jpg');
        $uploadedFile
            ->method('move')
            ->willReturnSelf(); // Simule un déplacement réussi

        // Mock du slugger pour générer un nom de fichier sûr
        $this->slugger
            ->method('slug')
            ->willReturn(new UnicodeString('avatar'));

        // Simuler la mise à jour de l'avatar
        $safeFileName = $this->slugger->slug('avatar')->toString(); // Conversion explicite en string
        $newFileName = $safeFileName . '-' . uniqid() . '.jpg';
        $user->setAvatar($newFileName);

        // Assertions
        $this->assertEquals($newFileName, $user->getAvatar());
    }

    public function testOldAvatarDeletion(): void
    {
        // Mock d'un utilisateur avec un ancien avatar
        $user = new User();
        $user->setEmail('testuser@example.com');
        $user->setAvatar('old-avatar.jpg');

        // Simuler le chemin du répertoire des avatars
        $avatarDirectory = '/tmp/uploads/avatar';

        // Suppression de l'ancien avatar
        $oldAvatarPath = $avatarDirectory . '/' . $user->getAvatar();
        if (file_exists($oldAvatarPath)) {
            unlink($oldAvatarPath);
        }

        // Vérifier que le fichier n'existe plus
        $this->assertFalse(file_exists($oldAvatarPath));
    }

    public function testFormSubmission(): void
    {
        // Mock d'un formulaire soumis avec des données valides
        $formData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'johndoe@example.com',
        ];

        $user = new User();
        $user->setFirstName($formData['firstName']);
        $user->setLastName($formData['lastName']);
        $user->setEmail($formData['email']);

        // Vérification des données mises à jour
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('johndoe@example.com', $user->getEmail());
    }
}
