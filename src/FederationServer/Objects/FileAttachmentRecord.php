<?php

    namespace FederationServer\Objects;

    use DateTime;
    use FederationServer\Interfaces\SerializableInterface;

    class FileAttachmentRecord implements SerializableInterface
    {
        private string $uuid;
        private string $evidence;
        private string $fileName;
        private int $fileSize;
        private string $fileMime;
        private int $created;

        /**
         * FileAttachmentRecord constructor.
         *
         * @param array $data Associative array of file attachment data.
         *                    - 'uuid': string, Unique identifier for the file attachment.
         *                    - 'evidence': string, UUID of the associated evidence record.
         *                    - 'file_name': string, Name of the file.
         *                    - 'file_size': int, Size of the file in bytes.
         *                    - 'file_mime': string, The MIME of the file
         *                    - 'created': int, Timestamp of when the record was created.
         */
        public function __construct(array $data)
        {
            $this->uuid = $data['uuid'] ?? '';
            $this->evidence = $data['evidence'] ?? '';
            $this->fileName = $data['file_name'] ?? '';
            $this->fileSize = isset($data['file_size']) ? (int)$data['file_size'] : 0;
            $this->fileMime = $data['file_mime'] ?? '';
            $this->created = isset($data['created']) ? (int)$data['created'] : time();
        }

        /**
         * Get the UUID of the file attachment.
         *
         * @return string
         */
        public function getUuid(): string
        {
            return $this->uuid;
        }

        /**
         * Get the UUID of the associated evidence record.
         *
         * @return string
         */
        public function getEvidence(): string
        {
            return $this->evidence;
        }

        /**
         * Get the name of the file.
         *
         * @return string
         */
        public function getFileName(): string
        {
            return $this->fileName;
        }

        /**
         * Get the size of the file in bytes.
         *
         * @return int
         */
        public function getFileSize(): int
        {
            return $this->fileSize;
        }

        /**
         * Get the MIME type of the file.
         *
         * @return string
         */
        public function getFileMime(): string
        {
            return $this->fileMime;
        }

        /**
         * Get the timestamp of when the record was created.
         *
         * @return int
         */
        public function getCreated(): int
        {
            return $this->created;
        }


        /**
         * @inheritDoc
         */
        public function toArray(): array
        {
            return [
                'uuid' => $this->uuid,
                'evidence' => $this->evidence,
                'file_name' => $this->fileName,
                'file_size' => $this->fileSize,
                'file_mime' => $this->fileMime,
                'created' => $this->created,
            ];
        }

        /**
         * @inheritDoc
         */
        public static function fromArray(array $array): SerializableInterface
        {
            if(isset($array['created']))
            {
                if(is_string($array['created']))
                {
                    $array['created'] = strtotime($array['created']);
                }
                elseif($array['created'] instanceof DateTime)
                {
                    $array['created'] = $array['created']->getTimestamp();
                }
            }

            return new self($array);
        }
    }