<?php
namespace app\rbac;

use yii\rbac\Item;
use yii\rbac\Rule;

/**
 * Проверяем authorID на соответствие с пользователем, переданным через параметры
 */
class AuthorRule extends Rule
{
    public $name = 'isAuthor';

    /**
     * @param string|int $user the user ID.
     * @param Item $item the role or permission that this rule is associated width.
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        if (isset($params['document'])) {
            return $params['document']->created_by == $user;
        }

        if (isset($params['file'])) {
            return $params['file']->created_by == $user;
        }

        if (isset($params['access'])) {
            return $params['access']->created_by == $user;
        }

        return false;
    }
}