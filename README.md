# doog
基于Zend Framework 2对一些组件再次抽象，丰富了使用方法，简化使用代码。

|模块           |描述             |
|---------------|-----------------|
|Authentication |ACL权限服务      |
|Db             |数据库操作       |
|EventModel     |事件管理器       |
|File           |文件上传下载     |
|Helpers        |助手             |
|Member         |用户             |
|Service        |服务管理器       |
|Mail           |邮件             |



## e.g.

* 获取一个单例模式的Zend Db
```php
Db::getInstance();
```

//todo: @liujin834 $$$$文档文档文档

* 给insert操作增加一个事件
```php
use Sookon\Db\Record\PreDeclare;
...
$this->getEventManager()->trigger(PreDeclare::EVENT_INSERT_PRE, $this, compact('ref'));
...
```

|事件                      |
|--------------------------|
|EVENT_INSERT_PRE          |
|EVENT_INSERT_PROCESS_DATA |
|EVENT_INSERT_POST         |
|EVENT_UPDATE_PRE          |
|EVENT_UPDATE_PROCESS_DATA |
|EVENT_UPDATE_POST         |
|EVENT_DELETE_PRE          |
|EVENT_DELETE_POST         |


//todo: @liujin834  $$$$文档文档文档
