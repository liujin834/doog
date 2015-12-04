<?php
/**
 * Created by PhpStorm.
 * User: liujin834
 * Date: 15-2-16
 * Time: 下午3:37
 */

namespace Sookon\Db\Record;

final class PreDeclare {
    const EVENT_INSERT_PRE = "insert.pre";
    const EVENT_INSERT_PROCESS_DATA = "insert.processData";
    const EVENT_INSERT_POST = "insert.post";

    const EVENT_UPDATE_PRE = "update.pre";
    const EVENT_UPDATE_PROCESS_DATA = "update.processData";
    const EVENT_UPDATE_POST = "update.post";

    const EVENT_DELETE_PRE = "delete.pre";
    const EVENT_DELETE_POST = "delete.post";
}