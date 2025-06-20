<?php

    namespace FederationServer\Methods\Entities;

    use FederationServer\Classes\Configuration;
    use FederationServer\Classes\Managers\EntitiesManager;
    use FederationServer\Classes\RequestHandler;
    use FederationServer\Classes\Utilities;
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

            if(
                !preg_match('#^/entities/([a-fA-F0-9\-]{36,})$#', FederationServer::getPath(), $matches) &&
                !preg_match('#^/entities/([a-f0-9\-]{64})$#', FederationServer::getPath(), $matches)
            )
            {
                throw new RequestException('Entity identifier is required', 400);
            }

            $entityIdentifier = $matches[1];
            if(!$entityIdentifier)
            {
                throw new RequestException('Entity Identifier SHA-256/UUID is required', 400);
            }

            try
            {
                if(Utilities::isUuid($entityIdentifier))
                {
                    $entityRecord = EntitiesManager::getEntityByUuid($entityIdentifier);
                }
                elseif(Utilities::isSha256($entityIdentifier))
                {
                    $entityRecord = EntitiesManager::getEntityByHash($entityIdentifier);
                }
                else
                {
                    throw new RequestException('Given identifier is not a valid UUID or SHA-256 input', 400);
                }
            }
            catch (DatabaseOperationException $e)
            {
                throw new RequestException('Unable to retrieve entity', 500, $e);
            }

            self::successResponse($entityRecord->toArray());
        }
    }

