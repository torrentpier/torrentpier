<h1>{L_MODS_MANAGER}</h1>

<h2>Список установленных модификаций</h2>
<table class="forumline">
    <thead>
    <tr>
        <th>#</th>
        <th>Название</th>
        <th>Автор</th>
        <th>Версия</th>
    </tr>
    </thead>
    <!-- BEGIN modifications_list -->
    <tbody>
    <tr class="{modifications_list.ROW_CLASS} tCenter">
        <td>{modifications_list.ROW_NUMBER}</td>
        <td>{modifications_list.MOD_NAME}</td>
        <td>{modifications_list.MOD_AUTHOR}</td>
        <td>{modifications_list.MOD_VERSION}</td>
    </tr>
    </tbody>
    <!-- END modifications_list -->
    <tfoot>
    <tr>
        <td class="catBottom warnColor1" colspan="4">Что-то тут...</td>
    </tr>
    </tfoot>
</table>

<br/>
<form action="admin_mods_manager.php" method="post">
    <table class="forumline">
        <tr>
            <th colspan="4">Установка модификаций</th>
        </tr>
        <tr class="row1">
            <td class="row1">
                <span class="gen"><b>Выберите файл (.xml):</b></span>
            </td>
            <td>
                <input type="file" name="avatar"/>
            </td>
        </tr>
        <tr>
            <td class="catBottom" colspan="2">
                <input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption"/>&nbsp;&nbsp;
                <input type="reset" value="{L_RESET}" class="liteoption"/>
            </td>
        </tr>
    </table>
</form>
