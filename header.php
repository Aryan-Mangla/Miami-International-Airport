<header class="sticky-top">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light" aria-label="Main Navbar" style="background-color: #fafafa; box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 12px;">
        <div class="container-fluid px-md-5 px-3">
            <div class="d-flex justify-content-between w-100">
                <a class="navbar-brand" href="/">
                    <img src="Pic/miami-international-airport.png" class="img542" alt="Miami Airport">
                </a>



                <form class="d-flex" action="search" method="get">
                    <input class="form-control me-2 d-lg-none d-none" type="search" placeholder="Search" aria-label="Search" name="query">
                    <button class="btn btn-outline-success d-lg-none d-none" type="submit">Search</button>
                </form>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            
            <div class="collapse navbar-collapse justify-content-end" id="mainNavbar">
                <ul class="navbar-nav">
                   
                    <li class="nav-item dropdown nav-hov">
                        <a class="nav-link dropdown-toggle link-dark" href="#" id="navbarDropdown1" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Flights
                        </a>
                        <ul class="dropdown-menu p-3" aria-labelledby="navbarDropdown1" style="width: 15em!important;">
                            <li><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Arrivals</a></li>
                            <li><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Departures</a></li>
                          
                        </ul>
                    </li>
                    <li class="nav-item  nav-hov">
                    <a class="nav-link link-dark" href="#">Terminals</a>
                </li>
                    <li class="nav-item dropdown nav-hov">
                        <a class="nav-link dropdown-toggle link-dark" href="#" id="navbarDropdown3" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Transportations
                        </a>
                        <ul class="dropdown-menu p-3 test54" aria-labelledby="navbarDropdown3">
                            <!-- Row 1 -->
                            <div class="row">
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Downtown Miami</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Fort Lauderdale</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to South Beach</a></div>
                            </div>
                            <!-- Row 1 end -->
                            <!-- Row 2 -->
                            <div class="row my-2">
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Cruise Port</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Boca Raton</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Islamorada</a></div>
                            </div>
                            <!-- Row 2 end -->
                            <!-- Row 3 -->
                            <div class="row my-2">
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Hollywood Beach</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Shuttle / Bus</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Train</a></div>
                            </div>
                            <!-- Row 3 end -->
                            <!-- Row 4 -->
                            <div class="row my-2">
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport Taxis</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Private Transfers</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Car Rentals</a></div>
                            </div>
                            <!-- Row 4 end -->
                        </ul>
                    </li>
                    <li class="nav-item  nav-hov">
                    <a class="nav-link link-dark" href="#">Parking</a>
                </li>
                    <li class="nav-item nav-hov">
                        <a class="nav-link link-dark" href="https://miamiairport-mia.com/blog">Blog</a>
                    </li>
                     <li class="nav-item dropdown">
  <button class="btn dropdown-toggle dropdown-toggle-none focus-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-search"></i>
  </button>
  <div class="dropdown-menu" style="width:15em;right:0em;">
    <form class="d-flex p-3" action="search" method="get">
  <div class="input-group">
    <input class="form-control border-secondary border-end-0" type="search" placeholder="Search" aria-label="Search" name="query">
    <button class="btn border-secondary border-start-0" type="submit">  <i class="fas fa-search"></i></button>
  </div>
</form>

  </div>
</li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Off-canvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasNavbarLabel"> 
                <a class="navbar-brand" href="/">
                    <img src="Pic/miami-international-airport.png" class="img542" alt="Miami Airport">
                </a>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
           
            <ul class="navbar-nav">
                 <li class="nav-item nav-hov dropdown">
  <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-search"></i>
  </button>
 <div class="dropdown-menu" style="width:15em;">
    <form class="d-flex p-3" action="search" method="get">
  <div class="input-group">
    <input class="form-control border-secondary border-end-0" type="search" placeholder="Search" aria-label="Search" name="query">
    <button class="btn border-secondary border-start-0" type="submit">  <i class="fas fa-search"></i></button>
  </div>
</form>

  </div>
</li>
                <li class="nav-item dropdown nav-hov">
                    <a class="nav-link dropdown-toggle link-dark" href="#" id="offcanvasDropdown1" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Flights
                    </a>
                    <ul class="dropdown-menu p-3" aria-labelledby="offcanvasDropdown1" style="width: 15em!important;">
                        <li><a href="flights-arrivals" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Arrivals</a></li>
                        <li><a href="flights-departures" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Departures</a></li>
                        <li><a href="flights-connections" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Connections</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown nav-hov">
                    <a class="nav-link dropdown-toggle link-dark" href="#" id="offcanvasDropdown2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Terminals
                    </a>
                    <ul class="dropdown-menu p-3" aria-labelledby="offcanvasDropdown2" style="width: 15em!important;">
                        <li><a href="terminal" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Terminal</a></li>
                        <li><a href="domestic-terminal" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Domestic Terminal</a></li>
                        <li><a href="international-terminal" class="text-decoration-none anchor my-md-0 my-2 nav-hov">International Terminal</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown nav-hov">
                        <a class="nav-link dropdown-toggle link-dark" href="https://miamiairport-mia.com/transportation/" id="navbarDropdown3" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Transportations
                        </a>
                        <ul class="dropdown-menu p-3 test54" aria-labelledby="navbarDropdown3">
                            <!-- Row 1 -->
                            <div class="row">
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Downtown Miami</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Fort Lauderdale</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to South Beach</a></div>
                            </div>
                            <!-- Row 1 end -->
                            <!-- Row 2 -->
                            <div class="row my-2">
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Cruise Port</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Boca Raton</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Islamorada</a></div>
                            </div>
                            <!-- Row 2 end -->
                            <!-- Row 3 -->
                            <div class="row my-2">
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport to Hollywood Beach</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Shuttle / Bus</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Train</a></div>
                            </div>
                            <!-- Row 3 end -->
                            <!-- Row 4 -->
                            <div class="row my-2">
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Airport Taxis</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Private Transfers</a></div>
                                <div class="col-md-4"><a href="#" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Car Rentals</a></div>
                            </div>
                            <!-- Row 4 end -->
                        </ul>
                    </li>
                <li class="nav-item dropdown nav-hov">
                    <a class="nav-link dropdown-toggle link-dark" href="#" id="offcanvasDropdown4" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Parking
                    </a>
                    <ul class="dropdown-menu p-3 test55" aria-labelledby="offcanvasDropdown4">
                        <!-- Row 1 -->
                        <div class="row">
                            <div class="col-md-4"><a href="parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Parking</a></div>
                            <div class="col-md-4"><a href="parking-rates" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Parking Rates</a></div>
                            <div class="col-md-4"><a href="overnight-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Overnight Parking</a></div>
                        </div>
                        <!-- Row 1 end -->
                        <!-- Row 2 -->
                        <div class="row my-2">
                            <div class="col-md-4"><a href="parking-lots" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Parking Lots</a></div>
                            <div class="col-md-4"><a href="off-site-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Off Site Parking</a></div>
                            <div class="col-md-4"><a href="domestic-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Domestic Parking</a></div>
                        </div>
                        <!-- Row 2 end -->
                        <!-- Row 3 -->
                        <div class="row my-2">
                            <div class="col-md-4"><a href="daily-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Daily Parking</a></div>
                            <div class="col-md-4"><a href="short-term-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Short Term Parking</a></div>
                            <div class="col-md-4"><a href="economy-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Economy Parking</a></div>
                        </div>
                        <!-- Row 3 end -->
                        <!-- Row 4 -->
                        <div class="row my-2">
                            <div class="col-md-4"><a href="affordable-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Affordable Parking</a></div>
                            <div class="col-md-4"><a href="north-terminal-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">North Terminal Parking</a></div>
                            <div class="col-md-4"><a href="south-terminal-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">South Terminal Parking</a></div>
                        </div>
                        <!-- Row 4 end -->
                        <!-- Row 5 -->
                        <div class="row">
                            <div class="col-md-4"><a href="cell-phone-lot" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Cell Phone Lot</a></div>
                            <div class="col-md-4"><a href="international-airport-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">International Airport Parking</a></div>
                            <div class="col-md-4"><a href="overnight-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Overnight Parking</a></div>
                        </div>
                        <!-- Row 5 end -->
                        <!-- Row 6 -->
                        <div class="row">
                            <div class="col-md-4"><a href="long-term-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Long Term Parking</a></div>
                            <div class="col-md-4"><a href="hourly-parking" class="text-decoration-none anchor my-md-0 my-2 nav-hov">Hourly Parking</a></div>
                        </div>
                        <!-- Row 6 end -->
                    </ul>
                </li>
                <li class="nav-item nav-hov">
                    <a class="nav-link link-dark" href="https://miamiairport-mia.com/blog">Blog</a>
                </li>
            </ul>
        </div>
    </div>
</header>
