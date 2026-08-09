<?php

// Here index.php is the main entrance page of the website

/* 
I use require_once because I need to load another
PHP file (database.php) and execute it.

once tells PHP to load that file only one time.

__DIR__ returns the folder where index.php is located
and . joins text together in PHP.
*/

require_once __DIR__ . "/config/database.php";

// Get all airports for the homepage flight search form
$sql = "SELECT id, city, country FROM airports ORDER BY city ASC";

$result = mysqli_query($conn, $sql);

// Convert the MySQL result into a normal PHP array
$airports = mysqli_fetch_all($result, MYSQLI_ASSOC);



// Load the header.
// header.php starts the session and opens the HTML page.
require_once __DIR__ . "/includes/header.php";

?>

<!-- Main content of the homepage -->
<main>

    <!-- Main introduction section -->
    <section class="hero">

        <!-- Keep the hero content centered -->
        <div class="hero-content">

            <!-- Text on the left side -->
            <div class="hero-text">

                <p class="small-title">
                    Know Before You Go 🌍
                </p>

                <?php if (isset($_SESSION["user_id"])) { ?>

                    <!-- Welcome the logged-in user -->
                    <p class="welcome">
                        Welcome,
                        <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
                    </p>

                <?php } ?>

                <h1>
                    Traveling opens the door to creating
                    <span>memories</span>
                </h1>

                <p class="hero-description">
                    Search for available flights, choose your destination
                    and book your next trip easily with TravelWorld.
                </p>

            </div>

            <!-- Images and video on the right side -->
            <div class="hero-images">

                <!-- First travel image -->
                <img
                    src="assets/images/hero-1.svg"
                    alt="Traveler discovering a mountain"
                >

                <!-- Travel video -->
                <video autoplay muted loop playsinline>

                    <source
                        src="assets/videos/hero-video.mp4"
                        type="video/mp4"
                    >

                    Your browser does not support videos.

                </video>

                <!-- Third travel image -->
                <img
                    src="assets/images/hero-3.svg"
                    alt="Traveler visiting a city"
                >

            </div>

        </div>

    </section>

        <!-- Flight search form -->
    <section class="search-section">

        <form action="flights.php" method="POST" class="search-form">

            <!-- Departure airport -->
            <div class="search-field">

                <label for="departure_airport_id">
                    Departure
                </label>

                <select
                    id="departure_airport_id"
                    name="departure_airport_id"
                    required
                >
                    <option value="">Where are you leaving from?</option>

                    <?php foreach ($airports as $airport) { ?>

                        <option value="<?php echo (int) $airport["id"]; ?>">
                            <?php
                            echo htmlspecialchars(
                                $airport["city"] . " - " . $airport["country"]
                            );
                            ?>
                        </option>

                    <?php } ?>

                </select>

            </div>

            <!-- Arrival airport -->
            <div class="search-field">

                <label for="arrival_airport_id">
                    Destination
                </label>

                <select
                    id="arrival_airport_id"
                    name="arrival_airport_id"
                    required
                >
                    <option value="">Where are you going?</option>

                    <?php foreach ($airports as $airport) { ?>

                        <option value="<?php echo (int) $airport["id"]; ?>">
                            <?php
                            echo htmlspecialchars(
                                $airport["city"] . " - " . $airport["country"]
                            );
                            ?>
                        </option>

                    <?php } ?>

                </select>

            </div>

            <!-- Search button -->
            <button type="submit" class="search-button">
                Search
            </button>

        </form>

    </section>

    <!-- Services section -->
<section class="services">

    <div class="services-container">

        <!-- Services title -->
        <div class="services-title">
            <p>What we offer</p>

            <h2>
                We offer our best services
            </h2>
        </div>

        <!-- First service -->
        <div class="service-card">

            <div class="service-icon">
                ✈
            </div>

            <h3>Easy Flight Search</h3>

            <p>
                Search available flights and choose your destination easily.
            </p>

        </div>


        <!-- Second service -->
        <div class="service-card">

            <div class="service-icon">
                🎫
            </div>

            <h3>Simple Booking</h3>

            <p>
                Enter your passenger information and book your flight easily.
            </p>

        </div>

        <!-- Third service -->
        <div class="service-card">

            <div class="service-icon">
                📋
            </div>

            <h3>Manage Reservations</h3>

            <p>
                View your booked flights and reservation information easily.
            </p>

        </div>

    </div>

</section>

<!-- Featured destinations section -->
<section class="destinations">
    <!-- Keep the destination content centered -->
     <div class="destinations-container">

    <!-- Destinations section title -->
        <div class="destinations-title">
            <p>Explore</p>
            <h2>Our featured destinations</h2>
        </div>


    <!-- Contains all destination images -->
        <div class="destination-layout">
            
            <!-- Left column for London and Dubai -->
                <div class="left-destinations">
                    
                <!-- London dsetination -->
                    <div class="destination-card">
                        <img src="assets/images/london.jpg" alt="Big Ben in London">
                    </div>

                <!-- Dubai destination -->   
                    <div class="destination-card">
                        <img src="assets/images/dubai.jpg" alt="Dubai in the UAE">
                    </div>

                </div>

                <!-- Middle column for Bali and Phetchabun -->
                 <div class="middle-destinations">

                    <!-- Bali destination -->
                     <div class="destination-card bali-card">
                        <img src="assets/images/bali.jpg" alt="Bali in Indonesia">
                     </div>

                    <!-- Phetchabun destination -->
                     <div class="destination-card phetchabun-card">
                        <img src="assets/images/phetchabun.jpg" alt="Phetchabun in Thailand">
                     </div>
                 </div>

                 <!-- Right column for Sydney, Paris and Wuxi -->
                  <div class="right-destinations">

                    <!-- Sydney destination -->
                     <div class="destination-card sydney-card">
                        <img src="assets/images/sydney.jpg" alt="Sydney in Australia">
                     </div>

                     <!-- Contains Paris and Wuxi -->
                      <div class="bottom-destinations">

                      <!-- Paris destination -->
                       <div class="destination-card paris-card">
                            <img src="assets/images/paris.jpg" alt="Paris in France">
                       </div>

                       <!-- Wuxi destination -->
                        <div class="destination-card wuxi-card">
                            <img src="assets/images/wuxi.jpg" alt="Wuxi in China">
                        </div>
                      </div>
                  </div>
        </div>

     </div>


</section>


<!-- Experience section -->
<section class="experience">

    <!-- Keep the experience content centered -->
    <div class="experience-container">

        <!-- Text on the left side -->
        <div class="experience-text">

            <p class="experience-small-title">
                Experience
            </p>

            <h2>
                With our experience, we make your journey easier
            </h2>

            <p class="experience-description">
                Search for flights, book your trip and manage your
                reservations easily with TravelWorld.
            </p>

            <!-- Experience statistics -->
                <div class="experience-stats">

                    <!-- Successful trips -->
                    <div class="stat">
                        <strong>12k+</strong>
                        <p>Successful trips</p>
                    </div>

                    <!-- Registered clients -->
                    <div class="stat">
                        <strong>2k+</strong>
                        <p>Registered clients</p>
                    </div>

                    <!-- Years of experience -->
                    <div class="stat">
                        <strong>15</strong>
                        <p>Years of experience</p>
                    </div>

                </div>

        </div>

        <!-- Image on the right side -->
            <div class="experience-image">

                <img
                    src="assets/images/experience.jpg"
                    alt="Traveler preparing for a trip"
                >

            </div>

    </div>

</section>


        <!-- Gallery section -->
            <section class="gallery">

                <!-- Keep the gallery content centered -->
                <div class="gallery-container">

                    <!-- Gallery section title -->
                    <div class="gallery-title">
                        <p>Gallery</p>
                        <h2>Visit our customers' travel gallery</h2>
                    </div>



                    <!-- Contains all gallery images -->
                    <div class="gallery-layout">

                        <!-- First gallery column -->
                            <div class="gallery-column">
                                    <!-- First gallery image -->
                                    <div class="gallery-card">
                                        <img
                                            src="assets/images/gallery-1.jpg"
                                            alt="Traveler hiking in the mountains"
                                        >
                                    </div>

                                    <!-- Second gallery image -->
                                    <div class="gallery-card">
                                        <img
                                            src="assets/images/gallery-2.jpg"
                                            alt="Boat traveling between tropical cliffs"
                                        >
                                    </div>
                            </div>

                        <!-- Second gallery column -->
                            <div class="gallery-column">

                                    <!-- Third gallery image -->
                                    <div class="gallery-card">
                                        <img
                                            src="assets/images/gallery-3.jpg"
                                            alt="Traveler enjoying a beach destination"
                                        >
                                    </div>

                                    <!-- Fourth gallery image -->
                                    <div class="gallery-card">
                                        <img
                                            src="assets/images/gallery-4.jpg"
                                            alt="Traveler exploring a city"
                                        >
                                    </div>
                            </div>

                        <!-- Third gallery column -->
                            <div class="gallery-column">
                                    <!-- Fifth gallery image -->
                                    <div class="gallery-card">
                                        <img
                                            src="assets/images/gallery-5.jpg"
                                            alt="Traveler enjoying a mountain view"
                                        >
                                    </div>

                                    <!-- Sixth gallery image -->
                                    <div class="gallery-card">
                                        <img
                                            src="assets/images/gallery-6.jpg"
                                            alt="Traveler enjoying a nature destination"
                                        >
                                    </div>
                            </div>

                        <!-- Fourth gallery column -->
                            <div class="gallery-column">
                                    <!-- Seventh gallery image -->
                                    <div class="gallery-card">
                                        <img
                                            src="assets/images/gallery-7.jpg"
                                            alt="Traveler exploring a beautiful destination"
                                        >
                                    </div>

                                    <!-- Eighth gallery image -->
                                    <div class="gallery-card">
                                        <img
                                            src="assets/images/gallery-8.jpg"
                                            alt="Traveler enjoying a scenic destination"
                                        >
                                    </div>
                            </div>


                    </div>

                </div>

            </section>



            <!-- Customer reviews section -->
                <section class="reviews">

                    <!-- Keep the reviews content centered -->
                    <div class="reviews-container">

                        <!-- Reviews section title -->
                        <div class="reviews-title">
                            <p>Fan Love</p>
                            <h2>What our customers say about us</h2>
                        </div>

                        <!-- Contains all customer reviews -->
                        <div class="reviews-list">
                            <!-- First customer review -->
                            <div class="review-card">

                                <p>
                                    TravelWorld made it easy for me to search for a flight
                                    and complete my reservation.
                                </p>

                                <h3>Sarah</h3>
                                <span>Customer</span>

                            </div>


                            <!-- Second customer review -->
                            <div class="review-card">

                                <p>
                                    I found my flight quickly and the booking process was simple
                                    and easy to understand.
                                </p>

                                <h3>Daniel</h3>
                                <span>Customer</span>

                            </div>



                            <!-- Third customer review -->
                            <div class="review-card">

                                <p>
                                    Managing my reservation was simple and I could see all my
                                    flight information in one place.
                                </p>

                                <h3>Emma</h3>
                                <span>Customer</span>

                            </div>
                        </div>

                    </div>

                </section>


                <!-- Newsletter section -->
<section class="newsletter">

    <div class="newsletter-container">

        <!-- Newsletter text -->
        <div class="newsletter-text">

            <h2>
                Subscribe now to get useful travel information
            </h2>

            <p>
                Stay updated with travel inspiration and news from TravelWorld.
            </p>

            <!-- Newsletter form used only for the design -->
            <form class="newsletter-form">

                <input
                    type="email"
                    placeholder="Enter your email"
                >

                <button type="button">
                    Subscribe
                </button>

            </form>

        </div>

        <!-- Newsletter image -->
                <div class="newsletter-image">
                    <img
                        src="assets/images/newsletter.png"
                        alt="Traveler receiving travel information"
                    >
                </div>

    </div>

</section>


</main>

<?php

// Load the footer.
// footer.php closes the body and HTML page.
require_once __DIR__ . "/includes/footer.php";

?>