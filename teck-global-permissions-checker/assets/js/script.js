jQuery(document).ready(function($) {
    // Basic table sorting (optional, can be expanded)
    var $table = $('#tgpchecker_permissions_table');
    $table.find('th').on('click', function() {
        var $th = $(this);
        var column = $th.index();
        var isDesc = $th.hasClass('sort-desc');
        $table.find('th').removeClass('sort-desc sort-asc');
        $th.addClass(isDesc ? 'sort-asc' : 'sort-desc');

        var rows = $table.find('tbody tr').get();
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq(column).text();
            var bVal = $(b).find('td').eq(column).text();
            return isDesc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });

        $.each(rows, function(index, row) {
            $table.find('tbody').append(row);
        });
    });
});
