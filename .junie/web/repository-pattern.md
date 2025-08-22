ComControl Web: Repository Pattern
=================================

This document describes the pattern used in the web application to add new repositories. It’s based on Symfony services, Doctrine, and Parthenon’s repository abstractions.

Overview
--------
There are two layers for repositories:

- Interface layer (App\Repository\*Interface) – the contract used in services/controllers.
- Domain repository (App\Repository\*) – extends a Parthenon base repository and implements your project’s interface. It receives a low-level entity repository via constructor injection.
- Orm repository (App\Repository\Orm\*) – low-level Doctrine repository wrapper extending Parthenon\Common\Repository\CustomServiceRepository to bind the entity class and ManagerRegistry.
- Service wiring (config/services.yaml) – aliases interfaces to concrete repositories, defines the Orm service, and injects it into the domain repository as $entityRepository.

1) Interface
------------
Create an interface in App\Repository. Example:

```php
<?php

namespace App\Repository;

use App\Entity\Question;
use App\Entity\Team;
use Parthenon\Athena\Repository\CrudRepositoryInterface;

interface QuestionRepositoryInterface extends CrudRepositoryInterface
{
    public function getUnansweredQuestion(Agent $agent): ?Question;

}
```

Notes:
- Define any project-specific methods here.
- Extend Parthenon\Athena\Repository\CrudRepositoryInterface.

2) Domain Repository (extends Parthenon or Doctrine Repository)
---------------------------------------------------
Create a class in App\Repository that extends the corresponding Parthenon repository and implements your interface. Use the injected $entityRepository (a Doctrine repository wrapper) for custom queries.

Example:

```php
<?php

namespace App\Repository;

use App\Entity\InviteCode;
use App\Entity\Team;
use Parthenon\Athena\Repository\DoctrineCrudRepository;

class QuestionRepository extends DoctrineCrudRepository implements InviteCodeRepositoryInterface
{
    public function getUnansweredQuestion(Agent $agent): ?Question;
    {
        return $this->entityRepository->findOneBy(['agent' => $agent, 'status' => 'unanswered']);
    }
}
```

Notes:
- The parent Parthenon repository expects an $entityRepository service to be injected (see services.yaml).
- Use $this->entityRepository for custom Doctrine queries.
- If you are not leveraging a Parthenon base repository for a new entity, you can alternatively extend Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository directly and follow a similar services.yaml wiring; however, in this codebase the preferred pattern is Parthenon base + injected Orm repository.
- Use $this->createQueryBuilder() for complex queries, as it provides a fluent interface for building Doctrine queries.

3) Orm Repository (extends CustomServiceRepository)
---------------------------------------------------
Create a class in App\Repository\Orm that binds the Doctrine ManagerRegistry to the specific entity. Always extend Parthenon\Common\Repository\CustomServiceRepository and pass the entity class to the parent constructor.

Example:

```php
<?php

namespace App\Repository\Orm;

use App\Entity\Question;
use Doctrine\Persistence\ManagerRegistry;
use Parthenon\Common\Repository\CustomServiceRepository;

class QuestionRepository extends CustomServiceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }
}
```

Notes:
- The class lives under App\Repository\Orm and is a thin wrapper; no custom methods are usually needed here.

4) Service Wiring (config/services.yaml)
----------------------------------------
Add/confirm the following entries in web/config/services.yaml:

- Alias the Parthenon interfaces to your domain repositories (if applicable).
- Define the domain repository service and inject the Orm repository into its $entityRepository argument.
- Register the Orm repository as a concrete service.

Pattern (actual examples from this project):

```yaml
services:
  # Interface to concrete bindings (Parthenon -> App)
  Parthenon\User\Repository\UserRepositoryInterface: '@App\Repository\UserRepository'
  Parthenon\User\Repository\TeamRepositoryInterface: '@App\Repository\TeamRepository'
  Parthenon\User\Repository\TeamInviteCodeRepositoryInterface: '@App\Repository\TeamInviteCodeRepository'

  # Domain repository receives the Orm repository as $entityRepository
  App\Repository\UserRepository:
    arguments:
      $entityRepository: '@app.repository.orm.user'
  app.repository.orm.user:
    class: App\Repository\Orm\UserRepository

  App\Repository\TeamRepository:
    arguments:
      $entityRepository: '@app.repository.orm.team'
  app.repository.orm.team:
    class: App\Repository\Orm\TeamRepository

  App\Repository\TeamInviteCodeRepository:
    arguments:
      $entityRepository: '@app.repository.orm.team_invite_code'
  app.repository.orm.team_invite_code:
    class: App\Repository\Orm\TeamInviteCodeRepository

  # Additional Parthenon repository wiring examples
  parthenon.user.repository.forgot_password_code_repository:
    class: Parthenon\User\Repository\ForgotPasswordCodeRepository
    arguments:
      - '@App\Repository\Orm\ForgotPasswordCodeRepository'

  parthenon.user.repository.orm.invite_code_repository_doctrine: '@App\Repository\Orm\InviteCodeRepository'
```

How to add a new repository (step-by-step)
------------------------------------------
1. Create Interface (App\Repository\YourEntityRepositoryInterface)
   - Extend an appropriate Parthenon interface if available; otherwise define methods as needed.
2. Create Domain Repository (App\Repository\YourEntityRepository)
   - Extend the corresponding Parthenon repository if one exists; implement your interface.
   - Use $this->entityRepository for custom queries.
3. Create Orm Repository (App\Repository\Orm\YourEntityRepository)
   - Extend CustomServiceRepository and pass the entity FQCN to the parent constructor.
4. Wire Services (config/services.yaml)
   - Alias interfaces to the domain repository if used by Parthenon or your code.
   - Define the domain repository service with argument $entityRepository: '@app.repository.orm.your_entity'.
   - Define the Orm repository service with class App\Repository\Orm\YourEntityRepository.

Naming and conventions
----------------------
- Interface: App\Repository\{Name}RepositoryInterface
- Domain repo: App\Repository\{Name}Repository
- Orm repo: App\Repository\Orm\{Name}Repository
- Service id for Orm repo: app.repository.orm.{snake_case_name}
- Keep domain-specific query logic in the domain repository. Orm repository remains a thin Doctrine adapter.

When to extend which base class
-------------------------------
- Domain repository: extend the relevant Parthenon repository (e.g., Parthenon\User\Repository\UserRepository) to inherit common behavior expected by the Parthenon bundle.
- Orm repository: always extend Parthenon\Common\Repository\CustomServiceRepository, which encapsulates Doctrine entity manager and the entity binding.

Examples in this codebase
-------------------------
- User
  - Interface: App\Repository\UserRepositoryInterface
  - Domain: App\Repository\UserRepository extends Parthenon\User\Repository\UserRepository
  - Orm: App\Repository\Orm\UserRepository extends CustomServiceRepository
  - services.yaml: binds interface and injects app.repository.orm.user

- Team
  - Interface: App\Repository\TeamRepositoryInterface
  - Domain: App\Repository\TeamRepository extends Parthenon\User\Repository\TeamRepository
  - Orm: App\Repository\Orm\TeamRepository extends CustomServiceRepository
  - services.yaml: injects app.repository.orm.team

- TeamInviteCode
  - Interface: App\Repository\TeamInviteCodeRepositoryInterface
  - Domain: App\Repository\TeamInviteCodeRepository extends Parthenon\User\Repository\TeamInviteCodeRepository
  - Orm: App\Repository\Orm\TeamInviteCodeRepository extends CustomServiceRepository
  - services.yaml: injects app.repository.orm.team_invite_code

- InviteCode and ForgotPasswordCode include similar wiring as illustrative variations.
