<?php declare(strict_types=1);

namespace App\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    private $em;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function createUser(string $email, string $plainPassword, array $roles = []): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (null === $user) {
            $user = new User();
            $user->setEmail($email);
            $this->em->persist($user);
        }
        $user->setRoles($roles);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $plainPassword));
        $this->em->flush();

        return $user;
    }
}
