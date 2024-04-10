document.addEventListener('DOMContentLoaded', function() {
    var configDiv = document.getElementById('config');
    var servicesDiv = document.getElementById('services');
    var wordpressUrlInput = document.getElementById('wordpressUrl');
    var usernameInput = document.getElementById('username');
    var passwordInput = document.getElementById('password');
    var saveConfigButton = document.getElementById('saveConfig');
    var servicesListDiv = document.getElementById('servicesList');
    var prevPageButton = document.getElementById('prevPage');
    var nextPageButton = document.getElementById('nextPage');
  
    var currentPage = 1;
    var perPage = 10;
  
    // Check if WordPress URL is already saved
    chrome.storage.sync.get(['wordpressUrl', 'username', 'password'], function(data) {
      if (data.wordpressUrl) {
        configDiv.style.display = 'none';
        servicesDiv.style.display = 'block';
        fetchServices(data.wordpressUrl, data.username, data.password);
      } else {
        configDiv.style.display = 'block';
        servicesDiv.style.display = 'none';
      }
    });
  
    // Save WordPress URL and credentials
    saveConfigButton.addEventListener('click', function() {
      var wordpressUrl = wordpressUrlInput.value;
      var username = usernameInput.value;
      var password = passwordInput.value;
  
      chrome.storage.sync.set({
        wordpressUrl: wordpressUrl,
        username: username,
        password: password
      }, function() {
        configDiv.style.display = 'none';
        servicesDiv.style.display = 'block';
        fetchServices(wordpressUrl, username, password);
      });
    });
  
    // Fetch services from WordPress
    function fetchServices(wordpressUrl, username, password) {
      // Make an API request to fetch services from WordPress
      // Use the wordpressUrl, username, and password to authenticate
      // Replace this with your actual API call
      var apiUrl = wordpressUrl + '/wp-json/homelab/v1/services';
    fetch(apiUrl, {
      headers: {
        'Authorization': 'Basic ' + btoa(username + ':' + password)
      }
    })
      .then(response => response.json())
      .then(data => {
        renderServices(data);
      })
      .catch(error => {
        console.error('Error fetching services:', error);
      });
  }
  
    // Render services on the popup
    function renderServices(services) {
      servicesListDiv.innerHTML = '';
  
      var startIndex = (currentPage - 1) * perPage;
      var endIndex = startIndex + perPage;
      var paginatedServices = services.slice(startIndex, endIndex);
  
      paginatedServices.forEach(function(service) {
        var serviceDiv = document.createElement('div');
        serviceDiv.className = 'service';
  
        var iconImg = document.createElement('img');
        iconImg.src = service.icon;
        iconImg.alt = 'Service Icon';
        serviceDiv.appendChild(iconImg);
  
        var nameSpan = document.createElement('span');
        nameSpan.textContent = service.name;
        serviceDiv.appendChild(nameSpan);
  
        var statusSpan = document.createElement('span');
        statusSpan.className = 'status ' + service.status.toLowerCase();
        serviceDiv.appendChild(statusSpan);
  
        var pinIcon = document.createElement('i');
        pinIcon.className = 'pin-icon';
        pinIcon.addEventListener('click', function() {
          togglePin(service.id);
        });
        serviceDiv.appendChild(pinIcon);
  
        servicesListDiv.appendChild(serviceDiv);
      });
  
      prevPageButton.disabled = currentPage === 1;
      nextPageButton.disabled = endIndex >= services.length;
    }
  
    // Toggle pin status of a service
    function togglePin(serviceId) {
      // Update the pin status of the service
      // You can store the pinned services in Chrome storage or make an API call to update the server
      // Replace this with your actual implementation
      console.log('Toggle pin for service:', serviceId);
    }
  
    // Previous page button click event
    prevPageButton.addEventListener('click', function() {
      if (currentPage > 1) {
        currentPage--;
        fetchServices(wordpressUrlInput.value, usernameInput.value, passwordInput.value);
      }
    });
  
    // Next page button click event
    nextPageButton.addEventListener('click', function() {
      currentPage++;
      fetchServices(wordpressUrlInput.value, usernameInput.value, passwordInput.value);
    });
  });