<?php


namespace atuin\installation\models;

use atuin\config\models\ModelConfig;
use atuin\installation\app_installation\helpers\ConfigFilesManager;
use atuin\installation\helpers\FileSystem;
use yii;
use yii\base\Model;

class ModelInstallation extends Model
{

    public $host;
    public $dbname;
    public $db_username;
    public $db_password;
    public $charset;
    public $title;


    public function rules()
    {
        return [
            [['host', 'dbname', 'db_username', 'db_password', 'charset', 'title'], 'string'],
            [['host', 'dbname', 'db_username', 'db_password', 'charset'], 'required', 'on' => 'database_installation'],
            [['host', 'dbname', 'db_username', 'db_password', 'charset'], 'validateDatabase'],
            [['title'], 'required', 'on' => 'username_installation'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'host' => Yii::t('atuin-installation', 'Database host'),
            'dbname' => Yii::t('atuin-installation', 'Database name'),
            'db_username' => Yii::t('atuin-installation', 'Database username'),
            'db_password' => Yii::t('atuin-installation', 'Database username password'),
            'charset' => Yii::t('atuin-installation', 'Charset'),
            'title' => Yii::t('atuin-installation', 'Web name'),
        ];
    }

    public function validateDatabase($attribute, $params)
    {

        $database_configuration = [
            "db_dsn" => 'mysql:host=' . $this->host . ';dbname=' . $this->dbname,
            "db_username" => $this->db_username,
            "db_password" => $this->db_password,
            "db_charset" => $this->charsetList($this->charset),
        ];

        $db = \Yii::$app->getDb();
        $db->dsn = $database_configuration['db_dsn'];
        $db->username = $database_configuration['db_username'];
        $db->password = $database_configuration['db_password'];
        $db->charset = $database_configuration['db_charset'];


        try
        {
            \Yii::$app->db->open();
        } catch (\Exception $e)
        {
            $this->addError($attribute, 'Couldnt connect to the database, check the configuration.');
        }
    }

    public function databaseInstallation()
    {

        $data =
            [
                'components' => [
                    'db' => [
                        'class' => 'yii\db\Connection',
                        'dsn' => 'mysql:host=' . $this->host . ';dbname=' . $this->dbname,
                        'username' => $this->db_username,
                        'password' => $this->db_password,
                        'charset' => $this->charsetList($this->charset),
                    ],
                ]
            ];

        $configPath = \Yii::$app->getModule('installation')->getSubdirectories('config');

        FileSystem::createFile($configPath . '/config-db.php', $data);

    }

    public function titleInstallation()
    {
        ModelConfig::addConfig(NULL, 'params', NULL, 'title', $this->title, FALSE);
        ConfigFilesManager::generateConfigFiles();
    }


    public function charsetList($id = NULL)
    {
        $list = [
            'big5',
            'dec8',
            'cp850',
            'hp8',
            'koi8r',
            'latin1',
            'latin2',
            'swe7',
            'ascii',
            'ujis',
            'sjis',
            'hebrew',
            'tis620',
            'euckr',
            'koi8u',
            'gb2312',
            'greek',
            'cp1250',
            'gbk',
            'latin5',
            'armscii8',
            'utf8',
            'ucs2',
            'cp866',
            'keybcs2',
            'macce',
            'macroman',
            'cp852',
            'latin7',
            'utf8mb4',
            'cp1251',
            'utf16',
            'cp1256',
            'cp1257',
            'utf32',
            'binary',
            'geostd8',
            'cp932',
            'eucjpms'
        ];

        if (is_null($id))
        {
            return $list;
        }

        return $list[$id];

    }


}