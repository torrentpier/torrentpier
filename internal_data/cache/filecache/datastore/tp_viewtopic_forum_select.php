<?php
if (!defined('BB_ROOT')) die(basename(__FILE__));
$filecache = array (
  'viewtopic_forum_select' => '
<select  name="new_forum_id" id="new_forum_id">
	<optgroup label="&nbsp;Новости">
		<option class="root_forum" value="67">&nbsp;Новости трекера&nbsp;</option>
		<option class="root_forum has_sf" value="70">&nbsp;Новости в сети&nbsp;</option>
		<option value="81">&nbsp; |- Новости игровой индустрии&nbsp;</option>
		<option value="79">&nbsp; |- События в мире&nbsp;</option>
		<option value="73">&nbsp; |- Новости Кино, Музыки . ТВ и Спорта&nbsp;</option>
		<option value="72">&nbsp; |- Новости мира IT технологий&nbsp;</option>
		<option class="root_forum" value="107">&nbsp;Radio Under&nbsp;</option>
	</optgroup>
	<optgroup label="&nbsp;Правила, инструкции, FAQ и т.д.">
		<option class="root_forum" value="106">&nbsp;F.A.Q. по релизам от Canek77&nbsp;</option>
		<option class="root_forum" value="82">&nbsp;Правила, инструкции, FAQ&nbsp;</option>
		<option class="root_forum" value="117">&nbsp;Оформление раздач&nbsp;</option>
		<option class="root_forum" value="101">&nbsp;Предложения и пожелания&nbsp;</option>
	</optgroup>
	<optgroup label="&nbsp;Награды">
		<option class="root_forum" value="89">&nbsp;Получение медали&nbsp;</option>
	</optgroup>
	<optgroup label="&nbsp;Игры">
		<option class="root_forum" value="2">&nbsp;Релизы CRACKSTATUS&nbsp;</option>
		<option class="root_forum" value="66">&nbsp;Горячие новинки&nbsp;</option>
		<option class="root_forum has_sf" value="5">&nbsp;Demo, Alpha, Beta версии (ранний доступ)&nbsp;</option>
		<option value="176">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="10">&nbsp;Экшены&nbsp;</option>
		<option value="52">&nbsp; |- Adventure&nbsp;</option>
		<option value="23">&nbsp; |- FPS (Шутеры от первого лица)&nbsp;</option>
		<option value="24">&nbsp; |- TPS (От третьего лица)&nbsp;</option>
		<option value="151">&nbsp; |- Slasher&nbsp;</option>
		<option value="51">&nbsp; |- Horror&nbsp;</option>
		<option value="50">&nbsp; |- Beat-em up&nbsp;</option>
		<option value="25">&nbsp; |- Stealth&nbsp;</option>
		<option value="177">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="11">&nbsp;Ролевые игры&nbsp;</option>
		<option value="56">&nbsp; |- Action (RPG)&nbsp;</option>
		<option value="57">&nbsp; |- Roguelike&nbsp;</option>
		<option value="58">&nbsp; |- Изометрические RPG&nbsp;</option>
		<option value="59">&nbsp; |- JRPG&nbsp;</option>
		<option value="178">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="6">&nbsp;Аркады&nbsp;</option>
		<option value="35">&nbsp; |- Платформеры&nbsp;</option>
		<option value="55">&nbsp; |- Метроидвания&nbsp;</option>
		<option value="179">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="7">&nbsp;Приключения и квесты&nbsp;</option>
		<option value="53">&nbsp; |- Point &amp; Click (Поиск предметов)&nbsp;</option>
		<option value="54">&nbsp; |- Визуальная новелла&nbsp;</option>
		<option value="180">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="8">&nbsp;Стратегии&nbsp;</option>
		<option value="21">&nbsp; |- TBS (пошаговые)&nbsp;</option>
		<option value="20">&nbsp; |- RTS (в реальном времени)&nbsp;</option>
		<option value="26">&nbsp; |- Экономические&nbsp;</option>
		<option value="181">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="12">&nbsp;Гонки&nbsp;</option>
		<option value="30">&nbsp; |- Аркады&nbsp;</option>
		<option value="27">&nbsp; |- Симуляторы&nbsp;</option>
		<option value="182">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="9">&nbsp;Симуляторы&nbsp;</option>
		<option value="61">&nbsp; |- Военные&nbsp;</option>
		<option value="28">&nbsp; |- Спортивные&nbsp;</option>
		<option value="60">&nbsp; |- Градостроительные симуляторы&nbsp;</option>
		<option value="32">&nbsp; |- Симуляторы жизни&nbsp;</option>
		<option value="29">&nbsp; |- Авиа/Космические&nbsp;</option>
		<option value="183">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="14">&nbsp;Инди&nbsp;</option>
		<option value="184">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="15">&nbsp;Файтинги&nbsp;</option>
		<option value="185">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="17">&nbsp;Хорроры&nbsp;</option>
		<option value="19">&nbsp; |- Survival horror&nbsp;</option>
		<option value="186">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="153">&nbsp;Логические игры&nbsp;</option>
		<option value="187">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="41">&nbsp;Интерактивное кино&nbsp;</option>
		<option value="42">&nbsp; |- Игры&nbsp;</option>
		<option value="43">&nbsp; |- Кино&nbsp;</option>
		<option value="188">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="37">&nbsp;Классика (старые игры)&nbsp;</option>
		<option value="189">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum has_sf" value="13">&nbsp;Онлайновые игры&nbsp;</option>
		<option value="33">&nbsp; |- FPS (Shooter)&nbsp;</option>
		<option value="34">&nbsp; |- TPS (От третьего лица)&nbsp;</option>
		<option value="22">&nbsp; |- MMO&nbsp;</option>
		<option value="36">&nbsp; |- Стратегии&nbsp;</option>
		<option value="152">&nbsp; |- Battle Royale (Королевская битва)&nbsp;</option>
		<option value="38">&nbsp; |- Спорт&nbsp;</option>
		<option value="190">&nbsp; |- Моды&nbsp;</option>
		<option class="root_forum" value="155">&nbsp;VR (Виртуальная реальность)&nbsp;</option>
		<option class="root_forum" value="154">&nbsp;Эротические игры (18+)&nbsp;</option>
		<option class="root_forum" value="149">&nbsp;Русификаторы&nbsp;</option>
		<option class="root_forum" value="62">&nbsp;Другие жанры&nbsp;</option>
		<option class="root_forum has_sf" value="171">&nbsp;Игры для Linux&nbsp;</option>
		<option value="173">&nbsp; |- Игры для Linux с Wine, DOSBox и другими&nbsp;</option>
		<option value="172">&nbsp; |- Нативные игры для Linux&nbsp;</option>
		<option class="root_forum has_sf" value="174">&nbsp;Прочие модификации&nbsp;</option>
		<option value="175">&nbsp; |- Beam NG Drive&nbsp;</option>
	</optgroup>
	<optgroup label="&nbsp;Софт">
		<option class="root_forum has_sf" value="109">&nbsp;ОС от Microsoft&nbsp;</option>
		<option value="115">&nbsp; |- Активаторы продуктов Microsoft&nbsp;</option>
		<option value="114">&nbsp; |- Патчи, дополнения для ОС от Microsoft&nbsp;</option>
		<option value="113">&nbsp; |- Windows 11&nbsp;</option>
		<option value="112">&nbsp; |- Windows 10&nbsp;</option>
		<option value="111">&nbsp; |- Windows 8 и 8.1&nbsp;</option>
		<option value="110">&nbsp; |- Windows 7&nbsp;</option>
		<option class="root_forum" value="123">&nbsp;Linux, Unix и другие ОС&nbsp;</option>
		<option class="root_forum has_sf" value="128">&nbsp;Системные программы&nbsp;</option>
		<option value="135">&nbsp; |- Драйверы и кодеки&nbsp;</option>
		<option value="148">&nbsp; |- Антивирусы, Файерволы и защита информации&nbsp;</option>
		<option value="134">&nbsp; |- Софт для тюнинга, твикинга&nbsp;</option>
		<option value="133">&nbsp; |- Настройка и обслуживание. Диагностика&nbsp;</option>
		<option value="131">&nbsp; |- Архиваторы и файловые менеджеры&nbsp;</option>
		<option value="132">&nbsp; |- Работа с жёстким диском&nbsp;</option>
		<option value="130">&nbsp; |- Работа с носителями информации&nbsp;</option>
		<option value="192">&nbsp; |- Сборники программ&nbsp;</option>
		<option value="129">&nbsp; |- Разное&nbsp;</option>
		<option class="root_forum has_sf" value="136">&nbsp;Пользовательские программы&nbsp;</option>
		<option value="191">&nbsp; |- Инструменты для установки Windows&nbsp;</option>
		<option value="140">&nbsp; |- Системы для офиса, бизнеса и научной работы&nbsp;</option>
		<option value="138">&nbsp; |- Программы для Интернет и сетей&nbsp;</option>
		<option value="139">&nbsp; |- Аудио- и видео-, CD- проигрыватели и каталогизаторы&nbsp;</option>
		<option value="137">&nbsp; |- Разное&nbsp;</option>
		<option class="root_forum has_sf" value="141">&nbsp;Создание и редактирование мультимедиа и 3D контента&nbsp;</option>
		<option value="147">&nbsp; |- Программные комплекты&nbsp;</option>
		<option value="146">&nbsp; |- Графические редакторы&nbsp;</option>
		<option value="145">&nbsp; |- Редакторы видео&nbsp;</option>
		<option value="144">&nbsp; |- Работа со звуком&nbsp;</option>
		<option value="143">&nbsp; |- Конвертеры&nbsp;</option>
		<option value="142">&nbsp; |- Разное&nbsp;</option>
		<option class="root_forum has_sf" value="164">&nbsp;Справочно-правовые системы&nbsp;</option>
		<option value="170">&nbsp; |- Консультант Плюс&nbsp;</option>
		<option value="169">&nbsp; |- Консультант Бухгалтер&nbsp;</option>
		<option value="168">&nbsp; |- Гарант&nbsp;</option>
		<option value="167">&nbsp; |- Кодекс&nbsp;</option>
		<option value="166">&nbsp; |- Другое&nbsp;</option>
		<option value="165">&nbsp; |- Архив&nbsp;</option>
	</optgroup>
	<optgroup label="&nbsp;Корзина">
		<option class="root_forum" value="63">&nbsp;Мусорка&nbsp;</option>
		<option class="root_forum" value="64">&nbsp;Отстойник&nbsp;</option>
	</optgroup>
	<optgroup label="&nbsp;Приватные форумы">
		<option class="root_forum has_sf" value="98">&nbsp;Админский&nbsp;</option>
		<option value="100">&nbsp; |- Баги&nbsp;</option>
		<option value="99">&nbsp; |- Моды&nbsp;</option>
	</optgroup>
</select>
',
);
?>