<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class MemberForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            $username = $this->username;
            $cacheKey = "failed_member_logins_{$username}";
            $failedLogins = Yii::$app->cache->get($cacheKey) ?: 0;

            if ($user) {
                // Cek apakah akun sedang disuspend
                if (isset($user->suspended_until) && $user->suspended_until && strtotime($user->suspended_until) > time()) {
                    $remaining = strtotime($user->suspended_until) - time();
                    $minutesLeft = ceil($remaining / 60);
                    $this->addError($attribute, "Akun ditangguhkan. Coba lagi dalam {$minutesLeft} menit.");
                    return;
                }

                if (!$user->validatePassword($this->password)) {
                    $failedLogins++;
                    Yii::$app->cache->set($cacheKey, $failedLogins, 300);

                    if ($failedLogins >= 5) {
                        // Suspend akun jika kolom suspended_until ada
                        if ($user->hasAttribute('suspended_until')) {
                            $user->suspended_until = date('Y-m-d H:i:s', time() + 300);
                            $user->save(false);
                        }
                        Yii::$app->cache->delete($cacheKey);
                        $this->addError($attribute, "Akun ditangguhkan selama 5 menit karena salah login 5x berturut-turut.");
                    } else {
                        $this->addError($attribute, "Username atau password salah. Sisa percobaan: " . (5 - $failedLogins));
                    }
                } else {
                    // Reset counter on successful password
                    Yii::$app->cache->delete($cacheKey);
                }
            } else {
                $failedLogins++;
                Yii::$app->cache->set($cacheKey, $failedLogins, 300);
                $this->addError($attribute, "Username atau password salah. Sisa percobaan: " . (5 - $failedLogins));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }

        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Member::findByUsername($this->username);
        }

        return $this->_user;
    }
}
