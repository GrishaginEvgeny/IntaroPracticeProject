<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\Admin\SectionCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // ...
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
            // ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
        ;
    }

    // public function detail(AdminContext $context)
    // {
    //     $event = new BeforeCrudActionEvent($context);
    //     $this->container->get('event_dispatcher')->dispatch($event);
    //     if ($event->isPropagationStopped()) {
    //         return $event->getResponse();
    //     }

    //     if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::DETAIL, 'entity' => $context->getEntity()])) {
    //         throw new ForbiddenActionException($context);
    //     }

    //     if (!$context->getEntity()->isAccessible()) {
    //         throw new InsufficientEntityPermissionException($context);
    //     }

    //     $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_DETAIL)));
    //     $context->getCrud()->setFieldAssets($this->getFieldAssets($context->getEntity()->getFields()));
    //     $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());
    //     $offers=$context->getEntity()->getInstance()->getOffers();  //если используем это - переписываем шаблон и возвращаем другие параметры

    //     $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
    //         'pageName' => Crud::PAGE_DETAIL,
    //         'templateName' => 'crud/detail',
    //         'entity' => $context->getEntity(),
    //     ]));

    //     $event = new AfterCrudActionEvent($context, $responseParameters);
    //     $this->container->get('event_dispatcher')->dispatch($event);
    //     if ($event->isPropagationStopped()) {
    //         return $event->getResponse();
    //     }

    //     return $responseParameters;
    // }

    // public function edit(AdminContext $context)
    // {
    //     $event = new BeforeCrudActionEvent($context);
    //     $this->container->get('event_dispatcher')->dispatch($event);
    //     if ($event->isPropagationStopped()) {
    //         return $event->getResponse();
    //     }

    //     if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::EDIT, 'entity' => $context->getEntity()])) {
    //         throw new ForbiddenActionException($context);
    //     }

    //     if (!$context->getEntity()->isAccessible()) {
    //         throw new InsufficientEntityPermissionException($context);
    //     }

    //     $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_EDIT)));
    //     $context->getCrud()->setFieldAssets($this->getFieldAssets($context->getEntity()->getFields()));
    //     $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());
    //     $entityInstance = $context->getEntity()->getInstance();

    //     if ($context->getRequest()->isXmlHttpRequest()) {
    //         if ('PATCH' !== $context->getRequest()->getMethod()) {
    //             throw new MethodNotAllowedHttpException(['PATCH']);
    //         }

    //         if (!$this->isCsrfTokenValid(BooleanField::CSRF_TOKEN_NAME, $context->getRequest()->query->get('csrfToken'))) {
    //             if (class_exists(InvalidCsrfTokenException::class)) {
    //                 throw new InvalidCsrfTokenException();
    //             } else {
    //                 return new Response('Invalid CSRF token.', 400);
    //             }
    //         }

    //         $fieldName = $context->getRequest()->query->get('fieldName');
    //         $newValue = 'true' === mb_strtolower($context->getRequest()->query->get('newValue'));

    //         try {
    //             $event = $this->ajaxEdit($context->getEntity(), $fieldName, $newValue);
    //         } catch (\Exception) {
    //             throw new BadRequestHttpException();
    //         }

    //         if ($event->isPropagationStopped()) {
    //             return $event->getResponse();
    //         }

    //         // cast to integer instead of string to avoid sending empty responses for 'false'
    //         return new Response((int) $newValue);
    //     }

    //     $editForm = $this->createEditForm($context->getEntity(), $context->getCrud()->getEditFormOptions(), $context);
    //     $editForm->handleRequest($context->getRequest());
    //     if ($editForm->isSubmitted() && $editForm->isValid()) {
    //         $this->processUploadedFiles($editForm);

    //         $event = new BeforeEntityUpdatedEvent($entityInstance);
    //         $this->container->get('event_dispatcher')->dispatch($event);
    //         $entityInstance = $event->getEntityInstance();

    //         $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

    //         $this->container->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));

    //         return $this->getRedirectResponseAfterSave($context, Action::EDIT);
    //     }

    //     $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
    //         'pageName' => Crud::PAGE_EDIT,
    //         'templateName' => 'crud/edit',
    //         'edit_form' => $editForm,
    //         'entity' => $context->getEntity(),
    //     ]));

    //     $event = new AfterCrudActionEvent($context, $responseParameters);
    //     $this->container->get('event_dispatcher')->dispatch($event);
    //     if ($event->isPropagationStopped()) {
    //         return $event->getResponse();
    //     }

    //     return $responseParameters;
    // }

    // public function new(AdminContext $context)
    // {
    //     $event = new BeforeCrudActionEvent($context);
    //     $this->container->get('event_dispatcher')->dispatch($event);
    //     if ($event->isPropagationStopped()) {
    //         return $event->getResponse();
    //     }

    //     if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::NEW, 'entity' => null])) {
    //         throw new ForbiddenActionException($context);
    //     }

    //     if (!$context->getEntity()->isAccessible()) {
    //         throw new InsufficientEntityPermissionException($context);
    //     }

    //     $context->getEntity()->setInstance($this->createEntity($context->getEntity()->getFqcn()));
    //     $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_NEW)));
    //     $context->getCrud()->setFieldAssets($this->getFieldAssets($context->getEntity()->getFields()));
    //     $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());

    //     $newForm = $this->createNewForm($context->getEntity(), $context->getCrud()->getNewFormOptions(), $context);
    //     $newForm->handleRequest($context->getRequest());

    //     $entityInstance = $newForm->getData();
    //     $context->getEntity()->setInstance($entityInstance);

    //     if ($newForm->isSubmitted() && $newForm->isValid()) {
    //         $this->processUploadedFiles($newForm);

    //         $event = new BeforeEntityPersistedEvent($entityInstance);
    //         $this->container->get('event_dispatcher')->dispatch($event);
    //         $entityInstance = $event->getEntityInstance();

    //         $this->persistEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

    //         $this->container->get('event_dispatcher')->dispatch(new AfterEntityPersistedEvent($entityInstance));
    //         $context->getEntity()->setInstance($entityInstance);

    //         return $this->getRedirectResponseAfterSave($context, Action::NEW);
    //     }

    //     $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
    //         'pageName' => Crud::PAGE_NEW,
    //         'templateName' => 'crud/new',
    //         'entity' => $context->getEntity(),
    //         'new_form' => $newForm,
    //     ]));

    //     $event = new AfterCrudActionEvent($context, $responseParameters);
    //     $this->container->get('event_dispatcher')->dispatch($event);
    //     if ($event->isPropagationStopped()) {
    //         return $event->getResponse();
    //     }

    //     return $responseParameters;
    // }

    // public function delete(AdminContext $context)
    // {
    //     $event = new BeforeCrudActionEvent($context);
    //     $this->container->get('event_dispatcher')->dispatch($event);
    //     if ($event->isPropagationStopped()) {
    //         return $event->getResponse();
    //     }

    //     if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::DELETE, 'entity' => $context->getEntity()])) {
    //         throw new ForbiddenActionException($context);
    //     }

    //     if (!$context->getEntity()->isAccessible()) {
    //         throw new InsufficientEntityPermissionException($context);
    //     }

    //     $csrfToken = $context->getRequest()->request->get('token');
    //     if ($this->container->has('security.csrf.token_manager') && !$this->isCsrfTokenValid('ea-delete', $csrfToken)) {
    //         return $this->redirectToRoute($context->getDashboardRouteName());
    //     }

    //     $entityInstance = $context->getEntity()->getInstance();

    //     $event = new BeforeEntityDeletedEvent($entityInstance);
    //     $this->container->get('event_dispatcher')->dispatch($event);
    //     if ($event->isPropagationStopped()) {
    //         return $event->getResponse();
    //     }
    //     $entityInstance = $event->getEntityInstance();

    //     try {
    //         $this->deleteEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);
    //     } catch (ForeignKeyConstraintViolationException $e) {
    //         throw new EntityRemoveException(['entity_name' => $context->getEntity()->getName(), 'message' => $e->getMessage()]);
    //     }

    //     $this->container->get('event_dispatcher')->dispatch(new AfterEntityDeletedEvent($entityInstance));

    //     $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
    //         'entity' => $context->getEntity(),
    //     ]));

    //     $event = new AfterCrudActionEvent($context, $responseParameters);
    //     $this->container->get('event_dispatcher')->dispatch($event);
    //     if ($event->isPropagationStopped()) {
    //         return $event->getResponse();
    //     }

    //     if (null !== $referrer = $context->getReferrer()) {
    //         return $this->redirect($referrer);
    //     }

    //     return $this->redirect($this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->unset(EA::ENTITY_ID)->generateUrl());
    // }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('vendor')
            ->add('sections')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield TextField::new('vendor');
        // yield AssociationField::new('offers');
        if (Crud::PAGE_INDEX === $pageName || $pageName ===Crud::PAGE_DETAIL){
            yield CollectionField::new('sections');
            // yield OfferCrudController::index();
        }
        else{
            yield AssociationField::new('sections')->setCrudController(SectionCrudController::class);
        }
        
    }
}
