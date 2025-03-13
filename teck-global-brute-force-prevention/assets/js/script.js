jQuery(document).ready(function($) {
    // IP Logs Table Sorting and Search
    var $table = $('#tgbfp_ip_table');
    var $search = $('#tgbfp_ip_search');

    $table.find('.sortable').on('click', function() {
        var $th = $(this);
        var column = $th.data('sort');
        var isDesc = $th.hasClass('sort-desc');
        $table.find('th').removeClass('sort-desc sort-asc');
        $th.addClass(isDesc ? 'sort-asc' : 'sort-desc');

        var rows = $table.find('tbody tr').get();
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq($th.index()).text();
            var bVal = $(b).find('td').eq($th.index()).text();
            return isDesc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });

        $.each(rows, function(index, row) {
            $table.find('tbody').append(row);
        });
    });

    $search.on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $table.find('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Google Maps Initialization
    if (typeof tgbfp_locations !== 'undefined' && typeof google !== 'undefined' && google.maps) {
        function initMap() {
            var map = new google.maps.Map(document.getElementById('tgbfp_map'), {
                zoom: 2,
                center: { lat: 0, lng: 0 }
            });

            Object.keys(tgbfp_locations).forEach(function(country) {
                var countryLocations = tgbfp_locations[country];
                var totalCount = countryLocations.reduce((sum, loc) => sum + loc.count, 0);
                var color = totalCount >= 26 ? '#ff0000' : totalCount >= 11 ? '#ffa500' : '#00ff00';

                countryLocations.forEach(function(location) {
                    var marker = new google.maps.Marker({
                        position: { lat: location.lat, lng: location.lng },
                        map: map,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            fillColor: color,
                            fillOpacity: 0.8,
                            strokeWeight: 0,
                            scale: 8
                        },
                        title: `${location.ip} (${totalCount})`
                    });
                });
            });
        }

        initMap();
    } else if (typeof tgbfp_locations !== 'undefined') {
        console.error('Google Maps API not loaded. Please ensure the API key is valid and the script is enqueued.');
    }
});
