<?php

    namespace FederationServer\Methods\Entities;

    use FederationServer\Classes\Configuration;
    use FederationServer\Classes\Managers\AuditLogManager;
    use FederationServer\Classes\Managers\EntitiesManager;
    use FederationServer\Classes\RequestHandler;
    use FederationServer\Classes\Utilities;
    use FederationServer\Exceptions\DatabaseOperationException;
    use FederationServer\Exceptions\RequestException;
    use FederationServer\FederationServer;

    class ListEntityAuditLogs extends RequestHandler
    {
        /**
         * @inheritDoc
         */
        public static function handleRequest(): void
        {
            $authenticatedOperator = FederationServer::getAuthenticatedOperator();
            if(!Configuration::getServerConfiguration()->isAuditLogsPublic() && $authenticatedOperator === null)
            {
                throw new RequestException('Public audit logs are disabled and no operator is authenticated', 403);
            }

            if(
                !preg_match('#^/entities/([a-fA-F0-9\-]{36,})/audit$#', FederationServer::getPath(), $matches) &&
                !preg_match('#^/entities/([a-f0-9\-]{64})/audit$#', FederationServer::getPath(), $matches)
            )
            {
                throw new RequestException('Entity identifier is required', 400);
            }

            $entityIdentifier = $matches[1];
            if(!$entityIdentifier)
            {
                throw new RequestException('Entity Identifier SHA-256/UUID is required', 400);
            }

            $limit = (int) (FederationServer::getParameter('limit') ?? Configuration::getServerConfiguration()->getListAuditLogsMaxItems());
            $page = (int) (FederationServer::getParameter('page') ?? 1);

            if($limit < 1 || $limit > Configuration::getServerConfiguration()->getListAuditLogsMaxItems())
            {
                $limit = Configuration::getServerConfiguration()->getListAuditLogsMaxItems();
            }

            if($page < 1)
            {
                $page = 1;
            }

            if($authenticatedOperator === null)
            {
                // Public audit logs are enabled, filter by public entries
                $filteredEntries = Configuration::getServerConfiguration()->getPublicAuditEntries();
            }
            else
            {
                // If an operator is authenticated, we can retrieve all entries
                $filteredEntries = null;
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

                self::successResponse(array_map(fn($log) => $log->toArray(),
                    AuditLogManager::getEntriesByEntity($entityRecord->getUuid(), $limit, $page, $filteredEntries))
                );
            }
            catch (DatabaseOperationException $e)
            {
                throw new RequestException('Unable to retrieve audit logs', 500, $e);
            }
        }
    }

