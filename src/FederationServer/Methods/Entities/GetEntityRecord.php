<?php

    namespace FederationServer\Methods\Entities;

    use FederationServer\Classes\Configuration;
    use FederationServer\Classes\Managers\EntitiesManager;
    use FederationServer\Classes\RequestHandler;
    use FederationServer\Classes\Validate;
    use FederationServer\Exceptions\DatabaseOperationException;
    use FederationServer\Exceptions\RequestException;
    use FederationServer\FederationServer;

    class GetEntityRecord extends RequestHandler
    {
        /**
         * @inheritDoc
         */
        public static function handleRequest(): void
        {
            $authenticatedOperator = FederationServer::getAuthenticatedOperator();
            if(!Configuration::getServerConfiguration()->isEntitiesPublic() && $authenticatedOperator === null)
            {
                throw new RequestException('You must be authenticated to view entity records', 401);
            }

            if(!preg_match('#^/entities/([a-fA-F0-9\-]{36,})$#', FederationServer::getPath(), $matches))
            {
                throw new RequestException('Entity UUID is required', 400);
            }

            $entityUuid = $matches[1];
            if(!$entityUuid || !Validate::uuid($entityUuid))
            {
                throw new RequestException('Entity UUID is required', 400);
            }


            try
            {
                $entityRecord = EntitiesManager::getEntityByUuid($entityUuid);
            }
            catch (DatabaseOperationException $e)
            {
                throw new RequestException('Unable to retrieve entity', 500, $e);
            }

            self::successResponse($entityRecord->toArray());
        }
    }

