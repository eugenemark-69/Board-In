</main>
  
  <!-- Add minimum height to main content to push footer down -->
  <style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    main {
      flex: 1;
      min-height: 60vh; /* Ensure main takes up most of viewport */
    }
  </style>
  
  <footer class="bg-light border-top mt-auto">
      <!-- Compact Footer Bar (Always Visible) -->
      <div class="container py-3">
        <div class="row align-items-center">
          <div class="col-md-6">
            <p class="text-muted small mb-0">© <?php echo date('Y'); ?> Board-In. All rights reserved.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <a href="#" class="text-decoration-none text-muted small me-3">Privacy</a>
            <a href="#" class="text-decoration-none text-muted small me-3">Terms</a>
            <a href="#" class="text-decoration-none text-muted small me-3">Security</a>
            <!-- Back to Top Button -->
            <a href="#" id="backToTop" class="btn btn-sm btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Back to top">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5a.5.5 0 0 0 .5.5z"/>
              </svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Expandable Footer Content (Hidden by Default, Shows on Hover/Click) -->
      <div id="expandedFooter" class="border-top" style="display: none;">
        <div class="container pt-4 pb-3">
          <div class="row mb-3">
            <!-- Board-In Section -->
            <div class="col-md-2 mb-3">
              <h6 class="fw-bold mb-3">Board-In</h6>
              <ul class="list-unstyled small">
                <li class="mb-2"><a href="/board-in/pages/index.php" class="text-decoration-none text-muted">Home</a></li>
                <li class="mb-2"><a href="/board-in/pages/about.php" class="text-decoration-none text-muted">About Us</a></li>
                <li class="mb-2"><a href="/board-in/pages/search.php" class="text-decoration-none text-muted">Browse Listings</a></li>
                <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Contact Us</a></li>
              </ul>
            </div>

            <!-- For Students Section -->
            <div class="col-md-2 mb-3">
              <h6 class="fw-bold mb-3">For Students</h6>
              <ul class="list-unstyled small">
                <li class="mb-2"><a href="/board-in/pages/search.php" class="text-decoration-none text-muted">Search Boarding Houses</a></li>
                <li class="mb-2"><a href="/board-in/pages/bookings.php" class="text-decoration-none text-muted">My Bookings</a></li>
                <li class="mb-2"><a href="/board-in/pages/favorites.php" class="text-decoration-none text-muted">Favorites</a></li>
                <li class="mb-2"><a href="/board-in/pages/reviews.php" class="text-decoration-none text-muted">Reviews</a></li>
              </ul>
            </div>

            <!-- For Landlords Section -->
            <div class="col-md-2 mb-3">
              <h6 class="fw-bold mb-3">For Landlords</h6>
              <ul class="list-unstyled small">
                <li class="mb-2"><a href="/board-in/pages/dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="mb-2"><a href="/board-in/pages/add-listing.php" class="text-decoration-none text-muted">Add Listing</a></li>
                <li class="mb-2"><a href="/board-in/pages/manage-listings.php" class="text-decoration-none text-muted">Manage Listings</a></li>
                <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Pricing</a></li>
              </ul>
            </div>

            <!-- Resources Section -->
            <div class="col-md-2 mb-3">
              <h6 class="fw-bold mb-3">Resources</h6>
              <ul class="list-unstyled small">
                <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Help Center</a></li>
                <li class="mb-2"><a href="#" class="text-decoration-none text-muted">FAQs</a></li>
                <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Safety Tips</a></li>
                <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Terms of Service</a></li>
              </ul>
            </div>

            <!-- Connect Section -->
            <div class="col-md-4 mb-3">
              <h6 class="fw-bold mb-3">Connect</h6>
              <p class="text-muted small mb-3">Find affordable boarding houses near BIPSU. Built for students.</p>
              
              <!-- Social Media Icons -->
              <div class="d-flex gap-2">
                <a href="https://facebook.com/" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" aria-label="Facebook">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>
                  </svg>
                </a>
                
                <a href="https://twitter.com/" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" aria-label="Twitter">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865l8.875 11.633Z"/>
                  </svg>
                </a>
                
                <a href="https://instagram.com/" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" aria-label="Instagram">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"/>
                  </svg>
                </a>
                
                <a href="https://www.linkedin.com/" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" aria-label="LinkedIn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zM4.943 12.306V5.337H2.542v6.969h2.401zm-1.2-7.94c.837 0 1.358-.554 1.358-1.248-.015-.71-.521-1.248-1.343-1.248C3.31 1.87 2.79 2.408 2.79 3.118c0 .694.52 1.248 1.344 1.248h.01zM13.458 12.306V8.354c0-2.128-1.139-3.118-2.657-3.118-1.222 0-1.761.675-2.066 1.151v.025h-.014V5.337H6.96c.03.708 0 6.969 0 6.969h2.401v-3.893c0-.208.015-.416.076-.565.166-.416.544-.847 1.179-.847.833 0 1.166.64 1.166 1.578v3.727h2.676z"/>
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Toggle Button -->
      <div class="text-center border-top py-2">
        <button id="toggleFooter" class="btn btn-sm btn-link text-muted text-decoration-none">
          <span id="toggleText">Show More</span>
          <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
          </svg>
        </button>
      </div>
    </footer>

    <!-- Footer Scripts -->
    <script>
      // Back to Top
      document.getElementById('backToTop').addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });

      // Toggle Footer
      const toggleBtn = document.getElementById('toggleFooter');
      const expandedFooter = document.getElementById('expandedFooter');
      const toggleText = document.getElementById('toggleText');
      const toggleIcon = document.getElementById('toggleIcon');
      let isExpanded = false;

      toggleBtn.addEventListener('click', function() {
        isExpanded = !isExpanded;
        
        if (isExpanded) {
          expandedFooter.style.display = 'block';
          toggleText.textContent = 'Show Less';
          toggleIcon.style.transform = 'rotate(180deg)';
        } else {
          expandedFooter.style.display = 'none';
          toggleText.textContent = 'Show More';
          toggleIcon.style.transform = 'rotate(0deg)';
        }
      });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
   
  </body>
</html>