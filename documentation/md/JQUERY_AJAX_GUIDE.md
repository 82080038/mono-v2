# jQuery & AJAX Complete Guide

## 🎯 Overview

jQuery adalah library JavaScript yang mempermudah manipulasi DOM, event handling, animation, dan AJAX. AJAX (Asynchronous JavaScript and XML) memungkinkan komunikasi dengan server tanpa reload halaman.

## 🚀 jQuery Basics

### **Installation**
```html
<!-- CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Local -->
<script src="jquery-3.7.1.min.js"></script>
```

### **Document Ready**
```javascript
// Classic way
$(document).ready(function() {
    // Your code here
});

// Shorthand (recommended)
$(function() {
    // Your code here
});

// Modern way (if jQuery loaded after DOM)
jQuery(function($) {
    // Your code here with $ as jQuery
});
```

### **Selectors**
```javascript
// Basic selectors
$("p")           // All <p> elements
$(".class")      // Elements with class="class"
$("#id")          // Element with id="id"
$("div.class")   // <div> with class="class"

// Attribute selectors
$("[href]")      // Elements with href attribute
$("[href='#']")  // Elements with href="#"
$("[type='text']") // Input type text

// Hierarchy selectors
$("parent > child")  // Direct child
$("ancestor descendant") // Any descendant
$("prev + next")      // Next sibling
$("prev ~ siblings")  // All following siblings
```

## 🎨 DOM Manipulation

### **Content Manipulation**
```javascript
// Get and set HTML
$("#element").html();           // Get HTML
$("#element").html("<p>New content</p>"); // Set HTML

// Get and set text
$("#element").text();           // Get text
$("#element").text("New text"); // Set text

// Get and set values
$("#input").val();              // Get value
$("#input").val("New value");   // Set value

// Get and set attributes
$("#img").attr("src");          // Get attribute
$("#img").attr("src", "new.jpg"); // Set attribute
$("#img").removeAttr("alt");     // Remove attribute
```

### **CSS Manipulation**
```javascript
// Add/remove classes
$("#element").addClass("new-class");
$("#element").removeClass("old-class");
$("#element").toggleClass("active");

// Check if has class
if ($("#element").hasClass("active")) {
    // Do something
}

// CSS manipulation
$("#element").css("color", "red");
$("#element").css({
    "color": "red",
    "font-size": "16px",
    "background": "#f0f0f0"
});

// Get CSS value
var color = $("#element").css("color");
```

### **Element Creation and Insertion**
```javascript
// Create elements
var newDiv = $("<div>", {
    "class": "new-div",
    "text": "New content",
    "id": "my-div"
});

// Insert elements
$("#parent").append(newDiv);        // As last child
$("#parent").prepend(newDiv);       // As first child
$("#element").after(newDiv);        // After element
$("#element").before(newDiv);       // Before element

// Insert multiple elements
$("#parent").append([
    "<p>Paragraph 1</p>",
    "<p>Paragraph 2</p>",
    newDiv
]);

// Remove elements
$("#element").remove();             // Remove element and data
$("#element").empty();              // Remove children only
$("#element").detach();             // Remove but keep data
```

## 🎯 Event Handling

### **Basic Events**
```javascript
// Click event
$("#button").click(function() {
    console.log("Button clicked");
});

// Multiple events
$("#element").on("click mouseenter", function() {
    $(this).toggleClass("active");
});

// Event with data
$("#button").on("click", {name: "John"}, function(event) {
    console.log("Hello " + event.data.name);
});

// Event delegation (for dynamically added elements)
$("#parent").on("click", ".child", function() {
    console.log("Child element clicked");
});
```

### **Common Events**
```javascript
// Form events
$("#form").submit(function(event) {
    event.preventDefault(); // Prevent default submission
    // Handle form submission
});

$("#input").focus(function() {
    $(this).addClass("focused");
});

$("#input").blur(function() {
    $(this).removeClass("focused");
});

$("#input").change(function() {
    console.log("Value changed to: " + $(this).val());
});

// Keyboard events
$(document).keydown(function(event) {
    if (event.which === 13) { // Enter key
        console.log("Enter pressed");
    }
});

// Mouse events
$("#element").hover(
    function() { $(this).addClass("hover"); },  // mouseenter
    function() { $(this).removeClass("hover"); } // mouseleave
);
```

### **Event Object**
```javascript
$("#element").click(function(event) {
    console.log("Event type: " + event.type);
    console.log("Target: " + event.target);
    console.log("Current target: " + event.currentTarget);
    console.log("Page X: " + event.pageX);
    console.log("Page Y: " + event.pageY);
    console.log("Which key: " + event.which);
    
    // Stop propagation
    event.stopPropagation();
    
    // Prevent default
    event.preventDefault();
});
```

## 🔄 AJAX Fundamentals

### **Basic AJAX Request**
```javascript
$.ajax({
    url: "api/endpoint.php",
    method: "POST",
    data: {name: "John", age: 30},
    dataType: "json",
    success: function(response) {
        console.log("Success:", response);
    },
    error: function(xhr, status, error) {
        console.error("Error:", error);
    },
    complete: function(xhr, status) {
        console.log("Request completed with status:", status);
    }
});
```

### **AJAX Shorthand Methods**
```javascript
// GET request
$.get("api/data.php", {id: 123}, function(response) {
    console.log(response);
}, "json");

// POST request
$.post("api/save.php", 
    {name: "John", email: "john@example.com"},
    function(response) {
        console.log("Data saved:", response);
    },
    "json"
);

// Load HTML content
$("#container").load("partial.html #section");

// GET JSON
$.getJSON("api/users.php", function(users) {
    console.log("Users:", users);
});
```

### **Advanced AJAX Configuration**
```javascript
$.ajax({
    url: "api/complex.php",
    method: "POST",
    data: JSON.stringify({name: "John", data: [1,2,3]}),
    contentType: "application/json",
    dataType: "json",
    timeout: 10000, // 10 seconds
    async: true,
    cache: false,
    
    // beforeSend callback
    beforeSend: function(xhr) {
        xhr.setRequestHeader("Authorization", "Bearer token");
        $("#loading").show();
    },
    
    // Success callback
    success: function(response, status, xhr) {
        console.log("Response:", response);
        console.log("Status:", status);
        console.log("XHR:", xhr);
    },
    
    // Error callback
    error: function(xhr, status, error) {
        console.error("Status:", status);
        console.error("Error:", error);
        console.error("Response text:", xhr.responseText);
        
        if (xhr.status === 404) {
            alert("Resource not found");
        } else if (xhr.status === 500) {
            alert("Server error");
        }
    },
    
    // Complete callback
    complete: function(xhr, status) {
        $("#loading").hide();
    }
});
```

## 🔧 AJAX Best Practices

### **Error Handling**
```javascript
function makeAjaxRequest(url, data) {
    return $.ajax({
        url: url,
        method: "POST",
        data: data,
        dataType: "json"
    })
    .fail(function(xhr, status, error) {
        console.error("AJAX failed:", error);
        
        // Handle different error types
        if (xhr.status === 0) {
            alert("Network error - check connection");
        } else if (xhr.status === 404) {
            alert("Resource not found");
        } else if (xhr.status === 500) {
            alert("Server error - try again later");
        } else {
            alert("Unknown error: " + xhr.status);
        }
    });
}

// Usage
makeAjaxRequest("api/save.php", {name: "John"})
    .done(function(response) {
        console.log("Success:", response);
    });
```

### **Loading States**
```javascript
// Show loading during AJAX
$("#form").submit(function(e) {
    e.preventDefault();
    
    var $button = $("#submit-btn");
    var originalText = $button.text();
    
    // Show loading state
    $button.prop("disabled", true).html('<i class="spinner"></i> Loading...');
    
    $.ajax({
        url: "api/submit.php",
        method: "POST",
        data: $(this).serialize(),
        dataType: "json"
    })
    .done(function(response) {
        if (response.success) {
            alert("Form submitted successfully!");
        } else {
            alert("Error: " + response.message);
        }
    })
    .fail(function() {
        alert("Submission failed. Please try again.");
    })
    .always(function() {
        // Restore button state
        $button.prop("disabled", false).text(originalText);
    });
});
```

### **AJAX with Forms**
```javascript
// Serialize form data
$("#myForm").submit(function(e) {
    e.preventDefault();
    
    var formData = $(this).serialize(); // name=value&name2=value2
    var formDataObj = $(this).serializeArray(); // Array of objects
    
    // File upload with FormData
    var formData = new FormData(this);
    
    $.ajax({
        url: "api/upload.php",
        method: "POST",
        data: formData,
        processData: false, // Important for FormData
        contentType: false, // Important for FormData
        success: function(response) {
            console.log("Upload successful:", response);
        }
    });
});
```

## 🎨 Animation & Effects

### **Basic Animations**
```javascript
// Show/hide
$("#element").hide();
$("#element").show();
$("#element").toggle();

// Fade
$("#element").fadeOut(1000); // 1 second
$("#element").fadeIn(1000);
$("#element").fadeToggle(1000);
$("#element").fadeTo(1000, 0.5); // Fade to 50% opacity

// Slide
$("#element").slideUp(1000);
$("#element").slideDown(1000);
$("#element").slideToggle(1000);
```

### **Custom Animations**
```javascript
// Animate CSS properties
$("#element").animate({
    "opacity": 0.5,
    "height": "200px",
    "width": "200px"
}, 1000, function() {
    console.log("Animation complete");
});

// Animate with easing
$("#element").animate({
    "left": "250px"
}, {
    duration: 1000,
    easing: "swing", // or "linear"
    complete: function() {
        console.log("Animation complete");
    }
});
```

### **Chaining Animations**
```javascript
$("#element")
    .fadeOut(500)
    .fadeIn(500)
    .slideUp(500)
    .slideDown(500);
```

## 🔧 Utilities

### **Each Loop**
```javascript
// Iterate over elements
$("li").each(function(index) {
    console.log("Item " + index + ": " + $(this).text());
});

// Iterate over array
$.each([1, 2, 3], function(index, value) {
    console.log("Index " + index + ": " + value);
});

// Iterate over object
$.each({name: "John", age: 30}, function(key, value) {
    console.log(key + ": " + value);
});
```

### **Data Storage**
```javascript
// Store data
$("#element").data("key", "value");
$("#element").data({name: "John", age: 30});

// Get data
var name = $("#element").data("name");
var allData = $("#element").data();

// Remove data
$("#element").removeData("key");
$("#element").removeData(); // Remove all data
```

### **Utilities**
```javascript
// Check if element exists
if ($("#element").length) {
    // Element exists
}

// Filter elements
$("div").filter(".active").css("background", "red");

// Find elements
$("#parent").find(".child"); // Find descendants
$("#parent").children(".child"); // Find direct children
$("#element").next(); // Next sibling
$("#element").prev(); // Previous sibling
$("#element").parent(); // Parent element
$("#element").closest(".container"); // Closest ancestor

// Extend objects
var obj1 = {a: 1, b: 2};
var obj2 = {b: 3, c: 4};
var merged = $.extend(obj1, obj2); // {a: 1, b: 3, c: 4}

// Deep extend
var deepMerged = $.extend(true, {}, obj1, obj2);
```

## 🎯 Common Patterns

### **Form Validation**
```javascript
$("#myForm").submit(function(e) {
    e.preventDefault();
    
    var isValid = true;
    var $email = $("#email");
    var $password = $("#password");
    
    // Reset errors
    $(".error").remove();
    $(".form-control").removeClass("is-invalid");
    
    // Validate email
    if (!$email.val().trim()) {
        $email.addClass("is-invalid")
              .after('<div class="error">Email is required</div>');
        isValid = false;
    } else if (!isValidEmail($email.val())) {
        $email.addClass("is-invalid")
              .after('<div class="error">Invalid email format</div>');
        isValid = false;
    }
    
    // Validate password
    if ($password.val().length < 6) {
        $password.addClass("is-invalid")
                .after('<div class="error">Password must be at least 6 characters</div>');
        isValid = false;
    }
    
    if (isValid) {
        // Submit form via AJAX
        submitForm();
    }
});

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
```

### **Dynamic Content Loading**
```javascript
function loadContent(page) {
    $("#loading").show();
    $("#content").fadeOut(300, function() {
        $.ajax({
            url: "pages/" + page + ".php",
            method: "GET",
            dataType: "html"
        })
        .done(function(html) {
            $("#content").html(html).fadeIn(300);
            updateNavigation(page);
        })
        .fail(function() {
            $("#content").html("<p>Error loading content</p>").fadeIn(300);
        })
        .always(function() {
            $("#loading").hide();
        });
    });
}
```

### **Auto-refresh Dashboard**
```javascript
function refreshDashboard() {
    $.ajax({
        url: "api/dashboard.php",
        method: "GET",
        dataType: "json"
    })
    .done(function(data) {
        updateStats(data.stats);
        updateChart(data.chart);
        updateActivity(data.activity);
    })
    .fail(function() {
        console.error("Failed to refresh dashboard");
    });
}

// Auto-refresh every 30 seconds
setInterval(refreshDashboard, 30000);
```

## 🔍 Debugging

### **Debugging AJAX**
```javascript
// Enable debugging
$.ajaxSetup({
    beforeSend: function(xhr, settings) {
        console.log("AJAX Request:", settings);
    },
    complete: function(xhr, status) {
        console.log("AJAX Response:", xhr.responseText);
    }
});

// Debug specific request
$.ajax({
    url: "api/test.php",
    method: "POST",
    data: {test: true},
    dataType: "json",
    success: function(response) {
        console.log("Success:", response);
    },
    error: function(xhr, status, error) {
        console.error("Error:", error);
        console.error("Response:", xhr.responseText);
        console.error("Status:", status);
    }
});
```

### **Common Issues & Solutions**
```javascript
// 1. AJAX not working - check if jQuery is loaded
if (typeof jQuery === 'undefined') {
    console.error("jQuery not loaded");
}

// 2. Event not firing - check if element exists
$(document).ready(function() {
    if ($("#button").length === 0) {
        console.error("Button not found");
    }
});

// 3. AJAX CORS issues
$.ajax({
    url: "api/data.php",
    method: "POST",
    crossDomain: true,
    xhrFields: {
        withCredentials: true
    }
});

// 4. Form serialization issues
var formData = $("#form").serialize();
console.log("Form data:", formData);
```

## 📚 Migration to Modern JavaScript

### **jQuery vs Vanilla JS**
```javascript
// jQuery
$("#element").text("Hello");
$("#element").addClass("active");
$("#element").on("click", function() { console.log("clicked"); });

// Vanilla JS
document.getElementById("element").textContent = "Hello";
document.getElementById("element").classList.add("active");
document.getElementById("element").addEventListener("click", function() { console.log("clicked"); });

// jQuery AJAX
$.ajax({url: "api/data", method: "GET", success: function(data) { console.log(data); }});

// Vanilla JS Fetch
fetch("api/data")
    .then(response => response.json())
    .then(data => console.log(data));
```

---

**📚 Resources:**
- [jQuery Official Documentation](https://jquery.com/)
- [jQuery API Reference](https://api.jquery.com/)
- [AJAX Guide](https://api.jquery.com/category/ajax/)
