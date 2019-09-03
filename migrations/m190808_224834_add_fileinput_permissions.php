<?php

use artsoft\db\PermissionsMigration;

class m190808_224834_add_fileinput_permissions extends PermissionsMigration
{

    public function beforeUp()
    {
        $this->addPermissionsGroup('fileinputManagement', 'Fileinput Management');
    }

    public function afterDown()
    {
        $this->deletePermissionsGroup('fileinputManagement');
    }

    public function getPermissions()
    {
        return [
            'fileinputManagement' => [
                'links' => [
                    '/admin/fileinput/file-manager/*',
                ],
                'uploadFile' => [
                    'title' => 'Upload FileInput',
                    'roles' => [self::ROLE_AUTHOR],
                    'links' => [
                        '/admin/fileinput/file-manager/file-upload',
                    ],
                ],   
                'sortFile' => [
                    'title' => 'Sort FileInput',
                    'roles' => [self::ROLE_AUTHOR],
                    'links' => [
                        '/admin/fileinput/file-manager/sort-file',
                    ],
                    'childs' => [
                        'uploadFile',
                    ],
                ],   
                'deleteFile' => [
                    'title' => 'Delete FileInput',
                    'roles' => [self::ROLE_AUTHOR],
                    'links' => [
                        '/admin/fileinput/file-manager/delete-file',
                    ],
                    'childs' => [
                        'uploadFile',
                        'sortFile',
                    ],
                ],   
                'fullFileinputAccess' => [
                    'title' => 'Full FileInput Access',
                    'roles' => [self::ROLE_MODERATOR],
                ],
            ],
        ];
    }

}
