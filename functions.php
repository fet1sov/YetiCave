<?php

const MINUTES_IN_HOUR = 60;
const SECOND_IN_MINUTE = 60;
const HOUR_IN_DAY = 24;

/**
* Форматирует число добавляя пробелы, пример: 1 000 000
* Также добавляет знак национальной валюты РФ
* @param integer $num Дата для валидации
*
* @return string Возвращает строку с форматрированным числом
*/
function make_number(int $num) : string
{
    return number_format($num, 0, '', ' ').' ₽';
} 

/**
* Форматирует число добавляя пробелы, пример: 1 000 000
* @param integer $num Дата для валидации
*
* @return string Возвращает строку с форматрированным числом
*/
function pretty_number(int $num) : string
{
    return number_format($num, 0, '', ' ');
}

/**
* Проверяет является ли дата в будующем или настоящем времени
* @param string $date Дата для валидации
*
* @return bool Возвращает true в случае если всё соотвестствует (false если дата не валидна или находится в прощедшем времени)
*/
function is_future_date(string $date) : bool
{
    if (is_date_valid($date))
    {
        $date = DateTime::createFromFormat('Y-m-d', $date);
        $today = new DateTime();

        return $date->getTimestamp() > $today->getTimestamp();
    } else {
        return false;
    }
}

const HOUR_SECONDS = 3600;
const MINUTE_SECONDS = 60;

/**
* Получает разницу между датами в часах, минутах, секундах (Пример: 02:28:12)
* @param string $date Дата для валидации
*
* @return array Возвращает массив с кол-вом часов и минут
*/
function get_dt_range(string $date) : array
{
    $date_diff = strtotime($date) - time();

    $hours = str_pad(floor($date_diff / HOUR_SECONDS), 2, "0", STR_PAD_LEFT);
    $minutes = str_pad((round($date_diff / MINUTE_SECONDS) % MINUTE_SECONDS), 2, "0", STR_PAD_LEFT);
    $seconds = str_pad((round($date_diff) % 60), 2, "0", STR_PAD_LEFT);

    return [$hours, $minutes, $seconds];
}

/**
* Получает список всех категорий имеющихся в базе данных
* @param mysqli $mysql Дата для валидации
*
* @return array Возвращает список всех лотов из базы данных
*/
function get_categories_list(mysqli $mysql) : array
{ 
    $sql_query = "SELECT * FROM category";
    $result = mysqli_query($mysql, $sql_query);

    return mysqli_fetch_all($result, MYSQLI_ASSOC) ?? [];
}

/**
* Получает список всех актуальных лотов
* @param mysqli $mysql Текущее подключение к базе данных
*
* @return array Возвращает список всех актуальных лотов
*/
function get_lot_list(mysqli $mysql) : array
{
    $sql_query = "SELECT `lot`.*, `category`.`name` AS `category_name`, COUNT(`bet`.`id`) AS `bet_count` FROM `lot` INNER JOIN `category` ON `lot`.`category_id` = `category`.`id` LEFT JOIN `bet` ON `bet`.`lot_id` = `lot`.`id` WHERE `lot`.`expire_date` >= CURRENT_TIMESTAMP GROUP BY `lot`.`id` ORDER BY `lot`.`date_create`;";
    $result = mysqli_query($mysql, $sql_query);

    return mysqli_fetch_all($result, MYSQLI_ASSOC) ?? [];
}

/**
* Получает список всех актуальных лотов
* @param mysqli $mysql Текущее подключение к базе данных
*
* @return array Возвращает список всех актуальных лотов
*/
function get_all_lot_list(mysqli $mysql) : array
{
    $sql_query = "SELECT `lot`.*, `category`.`name` AS `category_name`, COUNT(`bet`.`id`) AS `bet_count` FROM `lot` INNER JOIN `category` ON `lot`.`category_id` = `category`.`id` LEFT JOIN `bet` ON `bet`.`lot_id` = `lot`.`id` GROUP BY `lot`.`id` ORDER BY `lot`.`date_create`;";
    $result = mysqli_query($mysql, $sql_query);

    return mysqli_fetch_all($result, MYSQLI_ASSOC) ?? [];
}

/**
* Получить информацию о лоте по ID
* @param mysqli $mysql Текущее подключение к базе данных
* @param integer $id Идентификатор искомого лота в базе данных
*
* @return array Возвращает информацию о лоте
*/
function get_lot_info_by_id(mysqli $mysql, int $id) : array
{
    $sql_query = "SELECT `lot`.*, `category`.`name` AS `category_name` FROM `lot` INNER JOIN `category` ON `lot`.`category_id` = `category`.`id` WHERE `lot`.`id`=?";

    $stmt = mysqli_prepare($mysql, $sql_query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result) ?? [];
}

/**
* Получить список ставок на лот по его ID
* @param mysqli $mysql Текущее подключение к базе данных
* @param integer $id Идентификатор искомого лота в базе данных
*
* @return array Возвращает список ставок на лот
*/
function get_bet_list_by_lot_id(mysqli $mysql, int $id) : array | null
{
    $sql_query = "SELECT `bet`.`create_date`, `bet`.`summary`, `lot`.`name` AS `lot_name`, `account`.`name` AS `account_name`, `account`.`id` AS `user_id`
    FROM `bet` INNER JOIN `lot` ON `bet`.`lot_id` = `lot`.`id` INNER JOIN `account` ON `bet`.`user_id` = `account`.`id` WHERE `bet`.`lot_id` = ? ORDER BY `bet`.`create_date` DESC, `bet`.`summary` ASC";

    $stmt = mysqli_prepare($mysql, $sql_query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC) ?? null;
}

/**
* Получить список лотов по индентификатору категорию
* @param mysqli $mysql Текущее подключение к базе данных
* @param integer $id Идентификатор категории
*
* @return array Возвращает список лотов и их количество
*/
function get_lot_list_by_category_id(mysqli $mysql, int $id, int $elements, int $page) : array
{
    $sql_query = "SELECT `lot`.*, `category`.`name` AS `category_name`, COUNT(`bet`.`id`) AS `bet_count` FROM `lot` INNER JOIN `category` ON `lot`.`category_id` = `category`.`id` LEFT JOIN `bet` ON `bet`.`lot_id` = `lot`.`id` WHERE `lot`.`expire_date` >= CURRENT_TIMESTAMP AND `lot`.`category_id` = ? GROUP BY `lot`.`id` ORDER BY `lot`.`date_create` LIMIT ? OFFSET ?;";
    
    $stmt = mysqli_prepare($mysql, $sql_query);
    mysqli_stmt_bind_param($stmt, 'iii', $id, $elements, $page);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $sql_query = "SELECT `lot`.`id` FROM `lot` WHERE `lot`.`category_id` = ? AND `lot`.`expire_date` >= CURRENT_TIMESTAMP";

    $stmt = mysqli_prepare($mysql, $sql_query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return [mysqli_num_rows($result), $lots];
}

/**
* Получить название категории по индентификатору категории
* @param mysqli $mysql Текущее подключение к базе данных
* @param integer $id Идентификатор категории
*
* @return string Возвращает название категории
*/
function get_category_name_by_id(mysqli $mysql, int $category_id) : string
{
    $sql_query = "SELECT `category`.`name` FROM `category` WHERE `category`.`id` = ?";

    $stmt = mysqli_prepare($mysql, $sql_query);
    mysqli_stmt_bind_param($stmt, 'i', $category_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $category = mysqli_fetch_assoc($result);

    return $category['name'];
}

/**
* Добавляет лот в базу данных
* с указанными данными
* @param mysqli $mysql Текущее подключение к базе данных
* @param array $data Данные пользователя
*
* @return integer ID добавленного лота
*/
function add_lot_to_database(mysqli $mysql, array $data) : int
{
    $sql_query = "INSERT INTO `lot` (`name`, `description`, `expire_date`, `start_price`, `bet_step`, `author_id`, `category_id`, `image_link`) VALUES(?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($mysql, $sql_query);
    mysqli_stmt_bind_param($stmt, 'sssiiiis', 
    $data["lot-name"],
    $data["message"],
    $data["lot-date"],
    $data["lot-rate"],
    $data["lot-step"],
    $data["author_id"],
    $data["category"],
    $data['image_link']
    );

    mysqli_stmt_execute($stmt);

    return mysqli_insert_id($mysql);
}


/**
* Добавляет ставку к определённому лоту
* с указанными данными
* @param mysqli $mysql Текущее подключение к базе данных
* @param array $data Данные ставки (Сумма, ID пользователя, ID лота)
*
*/
function add_bet_to_lot(mysqli $mysql, array $data) : void
{
    $sql_query = "INSERT INTO `bet` (`summary`, `user_id`, `lot_id`) VALUES(?, ?, ?)";
    $stmt = mysqli_prepare($mysql, $sql_query);
    mysqli_stmt_bind_param($stmt, 'iii',
        $data['summary'],
        $data['user_id'],
        $data['lot_id'],
    );

    mysqli_stmt_execute($stmt);
}

/**
* Возвращает время в формате прошедших дней суток, часов и т. д.
* @param string $date Дата
*
* @return string Отформатированное время
*/
function format_time(string $date): string
{
    $date_time = strtotime($date);
    $diff = time() - $date_time;

    $day = floor($diff / (SECOND_IN_MINUTE * MINUTES_IN_HOUR * HOUR_IN_DAY));
    $hour = floor($diff / (SECOND_IN_MINUTE * MINUTES_IN_HOUR));
    $minute = floor($diff / SECOND_IN_MINUTE);

    $sec = $diff;

    if ($day >= 7) {
        return date('d.m.y в H:i', $date_time);
    }
    if ($day >= 1) {
        return $day . ' ' . get_noun_plural_form($day, 'день', 'дня', 'дней') . ' назад';
    }
    if ($hour >= 1) {
        return $hour . ' ' . get_noun_plural_form($hour, 'час', 'часа', 'часов') . ' назад';
    }
    if ($minute >= 1) {
        return $minute . ' ' . get_noun_plural_form($minute, 'минуту', 'минуты', 'минут') . ' назад';
    } 
    return $sec . ' ' . get_noun_plural_form($sec, 'секунду', 'секунды', 'секунд') . ' назад';
    
}

/**
* Получает значение поля при POST запросе
* @param string $name Название поля в POST запросе
*
* @return string Возвращает значение, иначе возвращает пустую строку
*/
function get_post_val(string $name) : string
{
    return $_POST[$name] ?? "";
}

/**
* Создаёт учётную запись в базе данных
* с указанными данными
* @param mysqli $mysql Текущее подключение к базе данных
* @param array $data Данные пользователя
*
* @return integer ID зарегистрированного пользователя
*/
function register_user(mysqli $mysql, array $data) : int | null
{
    $sql_query = "INSERT INTO `account` (`email`, `name`, `password`, `contacts`) VALUES(?, ?, ?, ?)";

    $userData = Array(
        "email" => strip_tags($data["email"]),
        "password" => password_hash($data['password'], PASSWORD_DEFAULT),
        "name" => strip_tags($data["name"]),
        "message" => strip_tags($data["message"]),
    );
    $stmt = mysqli_prepare($mysql, $sql_query);
    mysqli_stmt_bind_param($stmt, 'ssss', 
    $userData["email"],
    $userData["name"],
    $userData["password"],
    $userData["message"]
    );

    try { 
        mysqli_stmt_execute($stmt);
    } catch (mysqli_sql_exception $exception) {
        return null;
    }

    return mysqli_insert_id($mysql);
}

/**
* Получает информацию о пользователе
* по указанной электронной почте
* @param mysqli $mysql Текущее подключение к базе данных
* @param string $email Почта пользователя
*
* @return array Информация о пользователе
*/
function get_user_info_by_email(mysqli $mysql, string $email) : array | null
{
    $sql_query = "SELECT * FROM `account` WHERE `account`.`email`=?";

    $stmt = mysqli_prepare($mysql, $sql_query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result) ?? null;
}

/**
* Получает список совершонных ставок пользователем
* @param mysqli $mysql Текущее подключение к базе данных
* @param int $id ID пользователя
*
* @return array Список ставок совершонных пользователем
*/
function get_user_bets(mysqli $mysql, string $id) : array
{
    $sql_query = "SELECT `bet`.*, `lot`.`id` as `lot_id`, `lot`.`name` as `lot_name`, `lot`.`image_link` as `lot_img`, `lot`.`expire_date` as `expire_date`,
    `category`.`name` as `category_name`, `account`.`contacts` as `author_contacts`, `lot`.`winner_id` as `winner_id`
    FROM `bet`
    INNER JOIN `lot` ON `lot`.`id` = `bet`.`lot_id`
    INNER JOIN `category` ON `category`.`id` = `lot`.`category_id`
    INNER JOIN `account` ON `account`.`id` = `lot`.`author_id`
    WHERE `bet`.`user_id` = ?
    ORDER BY `expire_date` DESC";

    $stmt = mysqli_prepare($mysql, $sql_query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC) ?? [];
}

/**
* Функция проверяет были ли закончены торги у лота
* Если торги окончены, устанавливает победителя основываясь на самой большой ставке
* @param mysqli $mysql Текущее подключение к базе данных
* @param array $data Информация о лоте
*
* @return bool Возвращает true в случае если у лота был установлен победитель, false в противном случае
*/
function check_expire_lot_winner(mysqli $mysql, array $data) : bool
{
    if (strtotime('now') >= strtotime($data['expire_date']) && empty($data["winner_id"]))
    {
        $sql_query = "SELECT `bet`.`summary`, `bet`.`user_id` FROM `bet` WHERE `bet`.`lot_id` = ? ORDER BY `bet`.`summary` ASC";

        $stmt = mysqli_prepare($mysql, $sql_query);
        mysqli_stmt_bind_param($stmt, 'i', $data['id']);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $betters = mysqli_fetch_all($result, MYSQLI_ASSOC);

        if (!empty($betters))
        {
            $sql_query = "UPDATE `lot` SET `winner_id` = ? WHERE `id` = ?";

            $stmt = mysqli_prepare($mysql, $sql_query);
            mysqli_stmt_bind_param($stmt, 'ii', $betters[0]['user_id'], $data['id']);
            mysqli_stmt_execute($stmt);
        } else {
            $sql_query = "UPDATE `lot` SET `winner_id` = NULL WHERE `id` = ?";
            $stmt = mysqli_prepare($mysql, $sql_query);
            mysqli_stmt_bind_param($stmt, 'i', $data['id']);
            mysqli_stmt_execute($stmt);
        }

        return true;
    } else {
        return false;
    }
}

/**
* Ищет лоты по указанному имени
* @param mysqli $mysql Текущее подключение к базе данных
* @param string $term Название искомого лота
*
* @return array Найденные лоты (В противном случае возвращает пустой массив)
*/
function search_lots_by_name(mysqli $mysql, string $term, int $limit, int $offset) : array
{
    $sql_query = "";
    $stmt = null;
    $resultAll = null;

    if (!empty($term)) 
    {
        $sql_query = "SELECT `lot`.*, `category`.`name` AS `category_name` , COUNT(`bet`.`id`) AS `bet_count` FROM `lot` INNER JOIN `category` ON `lot`.`category_id` = `category`.`id` LEFT JOIN `bet` ON `bet`.`lot_id` = `lot`.`id` WHERE `lot`.`expire_date` >= CURRENT_TIMESTAMP AND MATCH(`lot`.`name`, `lot`.`description`) AGAINST(?) GROUP BY `lot`.`id`";
        $stmt = mysqli_prepare($mysql, $sql_query);
        mysqli_stmt_bind_param($stmt, 's', $term);
        mysqli_stmt_execute($stmt);
        $resultAll = mysqli_stmt_get_result($stmt);

        $sql_query = "SELECT `lot`.*, `category`.`name` AS `category_name` , COUNT(`bet`.`id`) AS `bet_count` FROM `lot` INNER JOIN `category` ON `lot`.`category_id` = `category`.`id` LEFT JOIN `bet` ON `bet`.`lot_id` = `lot`.`id` WHERE `lot`.`expire_date` >= CURRENT_TIMESTAMP AND MATCH(`lot`.`name`, `lot`.`description`) AGAINST(?) GROUP BY `lot`.`id` LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($mysql, $sql_query);
        mysqli_stmt_bind_param($stmt, 'sii', $term, $limit, $offset);
    } else {
        
        $sql_query = "SELECT `lot`.*, `category`.`name` AS `category_name`, COUNT(`bet`.`id`) AS `bet_count` FROM `lot` INNER JOIN `category` ON `lot`.`category_id` = `category`.`id` LEFT JOIN `bet` ON `bet`.`lot_id` = `lot`.`id` WHERE `lot`.`expire_date` >= CURRENT_TIMESTAMP GROUP BY `lot`.`id`";
        $stmt = mysqli_prepare($mysql, $sql_query);
        mysqli_stmt_execute($stmt);
        $resultAll = mysqli_stmt_get_result($stmt);

        $sql_query = "SELECT `lot`.*, `category`.`name` AS `category_name`, COUNT(`bet`.`id`) AS `bet_count` FROM `lot` INNER JOIN `category` ON `lot`.`category_id` = `category`.`id` LEFT JOIN `bet` ON `bet`.`lot_id` = `lot`.`id` WHERE `lot`.`expire_date` >= CURRENT_TIMESTAMP GROUP BY `lot`.`id` LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($mysql, $sql_query);
        mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

    return [mysqli_num_rows($resultAll), $rows] ?? [];
}