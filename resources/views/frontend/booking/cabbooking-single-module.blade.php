<!-- Cab Booking Start From Here -->
@php
   $dropLocation = getNomenclatureName('Enter Drop Location', true);
   $dropLocation = ($dropLocation=="Enter Drop Location")?__('Enter Drop Location'):__($dropLocation);
@endphp

<section class="cab-banner-area alTaxiBannerStart">
    <div class="container-fluid p-64 py-64">
        <div class="row align-items-center">
            <!-- Google Map - Shows on top for mobile, right side for desktop -->
            <div class="col-12 col-md-6 col-lg-7 col-xl-8 order-first order-md-last mb-4 mb-md-0">
                <div class="cab-map-container" style="border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                    <div id="cab-booking-map" style="width: 100%; height: 350px;"></div>
                </div>
            </div>
            <!-- Form Section -->
            <div class="col-12 col-md-6 col-lg-5 col-xl-4 order-last order-md-first">
                <div class="card-box mb-0">
                    <h2>{{ $homePageLabel->translations->first() ? $homePageLabel->translations->first()->title : '' }}
                    </h2>
                    <form
                        action="{{ route('categoryDetail', $homePageLabel->pickupCategories->first()->categoryDetail->slug ?? '') }}"
                        class="cab-booking-form">
                        <div class="cab-input">
                            <div class="form-group mb-1 position-relative"> <input class="form-control edit-other-stop pickup-location-input"
                                    type="text" placeholder="{{ __('Enter Pickup Location') }}"
                                    name="pickup_location" id="pickup_location_{{ $key }}"
                                    data-rel="{{ $key }}"> <input type="hidden"
                                    name="pickup_location_latitude" value=""
                                    id="pickup_location_{{ $key }}_latitude_home"
                                    data-rel="{{ $key }}" /> <input type="hidden"
                                    name="pickup_location_longitude" value=""
                                    id="pickup_location_{{ $key }}_longitude_home"
                                    data-rel="{{ $key }}" /> </div>
                                   <div class="form-group mb-0"> <input class="form-control edit-other-stop" type="text"
                                    name="destination_location" placeholder="{{$dropLocation}}"
                                    id="destination_location_{{ $key }}" data-rel="{{ $key }}">
                                   <input type="hidden" name="destination_location_latitude" value=""
                                    id="destination_location_{{ $key }}_latitude_home"
                                    data-rel="{{ $key }}" /> <input type="hidden"
                                    name="destination_location_longitude" value=""
                                    id="destination_location_{{ $key }}_longitude_home"
                                    data-rel="{{ $key }}" /> </div>
                            <div class="input-line"></div>
                        </div>
                        <div class="cab-footer"> <button
                                class="btn btn-solid new-btn request-btn">{{ __('Request Now') }}</button> <button
                                class="btn btn-solid new-btn schedule-btn">{{ __('Schedule For Later') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Initialize Cab Booking Map
var cabBookingMap;
var cabBookingMarker;
var riderMarkers = []; // Array to store rider markers

// Default location: Nairobi, Kenya
var defaultKenyaLocation = { lat: -1.286389, lng: 36.817223 };

// Marker icon paths
var riderMarkerIconAvailablePath = '{{ asset("demo/images/location.png") }}';
var riderMarkerIconUnavailablePath = '{{ asset("demo/images/location_grey.png") }}';

function initCabBookingMap() {
    // Initialize map with default Kenya location
    cabBookingMap = new google.maps.Map(document.getElementById('cab-booking-map'), {
        center: defaultKenyaLocation,
        zoom: 13,
        styles: [
            {
                "featureType": "poi",
                "elementType": "labels",
                "stylers": [{ "visibility": "off" }]
            }
        ]
    });

    // Create marker at default location
    cabBookingMarker = new google.maps.Marker({
        position: defaultKenyaLocation,
        map: cabBookingMap,
        draggable: true,
        animation: google.maps.Animation.DROP,
        title: 'Your Location'
    });

    // Try to get user's location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Success - user allowed location
                var userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                // Update map center and marker
                cabBookingMap.setCenter(userLocation);
                cabBookingMarker.setPosition(userLocation);
                
                // Auto-fill pickup location
                reverseGeocodeAndFillPickup(userLocation.lat, userLocation.lng);
                
                // Fetch and display nearby riders
                fetchNearbyRiders(userLocation.lat, userLocation.lng);
            },
            function(error) {
                // Error or denied - keep default Kenya location
                console.log('Geolocation error or denied, using default Kenya location');
            }
        );
    }

    // When marker is dragged, update pickup location
    google.maps.event.addListener(cabBookingMarker, 'dragend', function() {
        var position = cabBookingMarker.getPosition();
        reverseGeocodeAndFillPickup(position.lat(), position.lng());
    });

    // When map is clicked, move marker and update pickup location
    google.maps.event.addListener(cabBookingMap, 'click', function(event) {
        cabBookingMarker.setPosition(event.latLng);
        reverseGeocodeAndFillPickup(event.latLng.lat(), event.latLng.lng());
    });
}

function reverseGeocodeAndFillPickup(lat, lng) {
    var geocoder = new google.maps.Geocoder();
    var latlng = { lat: lat, lng: lng };
    
    geocoder.geocode({ location: latlng }, function(results, status) {
        if (status === 'OK' && results[0]) {
            var address = results[0].formatted_address;
            
            // Fill pickup location inputs
            $('input[name="pickup_location"]').each(function() {
                $(this).val(address);
                var inputId = $(this).attr('id');
                if (inputId) {
                    $('#' + inputId + '_latitude_home').val(lat);
                    $('#' + inputId + '_longitude_home').val(lng);
                }
            });
        }
    });
}

// Function to clear all rider markers
function clearRiderMarkers() {
    for (var i = 0; i < riderMarkers.length; i++) {
        riderMarkers[i].setMap(null);
    }
    riderMarkers = [];
}

// Function to fetch nearby riders from API
function fetchNearbyRiders(lat, lng) {
    // Clear existing markers
    clearRiderMarkers();
    
    // API endpoint - adjust if needed based on your setup
    var apiUrl = '{{ url("/api/v1/get/agents") }}';
    
    // Make API call to get nearby riders
    $.ajax({
        url: apiUrl,
        type: 'POST',
        data: {
            latitude: lat,
            longitude: lng
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        success: function(response) {
            if (response && response.status === 200 && response.data && response.data.length > 0) {
                // Display riders on map
                displayRidersOnMap(response.data);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error fetching nearby riders:', error);
            // Silently fail - don't show riders if API fails
        }
    });
}

// Function to display riders on map
function displayRidersOnMap(riders) {
    riders.forEach(function(rider) {
        // Get rider location from agentlog
        var riderLat = 0;
        var riderLng = 0;
        
        if (rider.agentlog && rider.agentlog.lat && rider.agentlog.long) {
            riderLat = parseFloat(rider.agentlog.lat);
            riderLng = parseFloat(rider.agentlog.long);
        } else if (rider.lat && rider.long) {
            riderLat = parseFloat(rider.lat);
            riderLng = parseFloat(rider.long);
        }
        
        // Skip if invalid coordinates
        if (riderLat === 0 || riderLng === 0 || isNaN(riderLat) || isNaN(riderLng)) {
            return;
        }
        
        // Determine marker icon based on availability
        var markerIconPath = (rider.is_available == 1) ? riderMarkerIconAvailablePath : riderMarkerIconUnavailablePath;
        var markerIcon = {
            url: markerIconPath,
            scaledSize: new google.maps.Size(50, 50),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(22, 22)
        };
        
        // Create marker
        var riderMarker = new google.maps.Marker({
            position: { lat: riderLat, lng: riderLng },
            map: cabBookingMap,
            icon: markerIcon,
            title: rider.agent_name || 'Rider'
        });
        
        // Create info window content
        var infoContent = '<div style="padding: 10px; min-width: 200px;">' +
            '<h6 style="margin: 0 0 8px 0; font-weight: bold;">' + (rider.agent_name || 'Rider') + '</h6>';
        
        if (rider.phone_number) {
            infoContent += '<p style="margin: 4px 0;"><i class="fas fa-phone-alt"></i> ' + rider.phone_number + '</p>';
        }
        
        if (rider.distance !== undefined) {
            infoContent += '<p style="margin: 4px 0;"><i class="fas fa-map-marker-alt"></i> ' + 
                parseFloat(rider.distance).toFixed(2) + ' ' + (rider.distance_type || 'km') + ' away</p>';
        }
        
        if (rider.arrival_time) {
            infoContent += '<p style="margin: 4px 0;"><i class="far fa-clock"></i> ETA: ' + rider.arrival_time + '</p>';
        }
        
        infoContent += '</div>';
        
        var infoWindow = new google.maps.InfoWindow({
            content: infoContent
        });
        
        // Add click listener to show info window
        riderMarker.addListener('click', function() {
            infoWindow.open(cabBookingMap, riderMarker);
        });
        
        // Store marker in array
        riderMarkers.push(riderMarker);
    });
}

// Initialize map when Google Maps API is loaded
if (typeof google !== 'undefined' && google.maps) {
    google.maps.event.addDomListener(window, 'load', initCabBookingMap);
} else {
    // Wait for Google Maps to load
    document.addEventListener('DOMContentLoaded', function() {
        var checkGoogleMaps = setInterval(function() {
            if (typeof google !== 'undefined' && google.maps) {
                clearInterval(checkGoogleMaps);
                initCabBookingMap();
            }
        }, 100);
    });
}
</script>
<!-- Cab Content Area Start From Here -->
