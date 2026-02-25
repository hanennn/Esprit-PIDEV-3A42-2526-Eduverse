(function ($) {
    'use strict';

    // Sticky Menu
    $(window).scroll(function () {
        if ($('header').offset().top > 10) {
            $('.top-header').addClass('hide');
            $('.navigation').addClass('nav-bg');
        } else {
            $('.top-header').removeClass('hide');
            $('.navigation').removeClass('nav-bg');
        }
    });

    // Background-images
    $('[data-background]').each(function () {
        $(this).css({
            'background-image': 'url(' + $(this).data('background') + ')'
        });
    });

    //Hero Slider
    $('.hero-slider').slick({
        autoplay: true,
        autoplaySpeed: 7500,
        pauseOnFocus: false,
        pauseOnHover: false,
        infinite: true,
        arrows: true,
        fade: true,
        prevArrow: '<button type=\'button\' class=\'prevArrow\'><i class=\'ti-angle-left\'></i></button>',
        nextArrow: '<button type=\'button\' class=\'nextArrow\'><i class=\'ti-angle-right\'></i></button>',
        dots: true
    });
    $('.hero-slider').slickAnimation();

    // venobox popup
    $(document).ready(function(){
        $('.venobox').venobox(); 
    });

    
    // mixitup filter
    var containerEl = document.querySelector('[data-ref~="mixitup-container"]');
    var mixer;
    if (containerEl) {
        mixer = mixitup(containerEl, {
            selectors: {
                target: '[data-ref~="mixitup-target"]'
            }
        });
    }

    //  Count Up
    function counter() {
        var oTop;
        if ($('.count').length !== 0) {
            oTop = $('.count').offset().top - window.innerHeight;
        }
        if ($(window).scrollTop() > oTop) {
            $('.count').each(function () {
                var $this = $(this),
                    countTo = $this.attr('data-count');
                $({
                    countNum: $this.text()
                }).animate({
                    countNum: countTo
                }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function () {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function () {
                        $this.text(this.countNum);
                    }
                });
            });
        }
    }
    $(window).on('scroll', function () {
        counter();
    });

})(jQuery);



//User.html JS 
// Student Dashboard Custom JavaScript

// Display current year in footer
document.addEventListener('DOMContentLoaded', function() {
  var CurrentYear = new Date().getFullYear();
  var yearElements = document.querySelectorAll('.copyright script');
  yearElements.forEach(function(element) {
    var yearSpan = document.createElement('span');
    yearSpan.textContent = CurrentYear;
    element.parentNode.insertBefore(yearSpan, element);
    element.remove();
  });
});

//sign up page custom js
// Update the hidden accountType field when radio buttons change
document.addEventListener('DOMContentLoaded', function() {
    const studentCard = document.getElementById('studentCard');
    const teacherCard = document.getElementById('teacherCard');
    const radioButtons = document.querySelectorAll('input[name="accountType"]');
    const hiddenAccountType = document.querySelector('select[id*="accountType"]') || 
                              document.querySelector('input[id*="accountType"]');
    const teacherFields = document.getElementById('teacherFields');

    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            // Update visual state
            studentCard.classList.remove('active');
            teacherCard.classList.remove('active');
            
            if (this.value === 'student') {
                studentCard.classList.add('active');
                if (hiddenAccountType) hiddenAccountType.value = 'ROLE_STUDENT';
                // Hide teacher fields
                if (teacherFields) teacherFields.style.display = 'none';
            } else {
                teacherCard.classList.add('active');
                if (hiddenAccountType) hiddenAccountType.value = 'ROLE_TEACHER';
                // Show teacher fields
                if (teacherFields) teacherFields.style.display = 'block';
            }
        });
    });

    // Initialize on page load - check which radio is selected
    const checkedRadio = document.querySelector('input[name="accountType"]:checked');
    if (checkedRadio && checkedRadio.value === 'teacher') {
        if (teacherFields) teacherFields.style.display = 'block';
    }
});
//User page custom js
// Student Dashboard Custom JavaScript

// Display current year in footer
document.addEventListener('DOMContentLoaded', function() {
  var CurrentYear = new Date().getFullYear();
  var yearElements = document.querySelectorAll('.copyright script');
  yearElements.forEach(function(element) {
    var yearSpan = document.createElement('span');
    yearSpan.textContent = CurrentYear;
    element.parentNode.insertBefore(yearSpan, element);
    element.remove();
  });
});



// Login Page Custom JavaScript

document.addEventListener('DOMContentLoaded', function() {
  
  // Display current year in footer
  var CurrentYear = new Date().getFullYear();
  var yearSpan = document.getElementById('currentYear');
  if (yearSpan) {
    yearSpan.textContent = CurrentYear;
  }

  // Password Toggle
  const togglePassword = document.getElementById('togglePassword');
  if (togglePassword) {
    togglePassword.addEventListener('click', function () {
      const password = document.getElementById('password');
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('ti-eye');
      this.classList.toggle('ti-eye-off');
    });
  }

});


// Login Page Custom JavaScript
// Formateur Profile Page Custom JavaScript

document.addEventListener('DOMContentLoaded', function() {
  
  // Display current year in footer
  var CurrentYear = new Date().getFullYear();
  var yearSpan = document.getElementById('currentYear');
  if (yearSpan) {
    yearSpan.textContent = CurrentYear;
  }

  // Inline editing for profile fields
  const editToggles = document.querySelectorAll('.edit-icon');
  const formActions = document.getElementById('formActions');
  const cancelBtn = document.getElementById('cancelEdit');
  const profileForm = document.getElementById('profileForm');
  let originalValues = {};

  console.log('Edit toggles found:', editToggles.length); // Debug
  console.log('Form actions:', formActions); // Debug

  editToggles.forEach(toggle => {
      toggle.addEventListener('click', function(e) {
          e.preventDefault();
          console.log('Edit clicked'); // Debug
          
          const field = this.getAttribute('data-field');
          const display = document.querySelector(`.field-display[data-field="${field}"]`);
          const input = document.querySelector(`.field-input[data-field="${field}"]`);

          console.log('Field:', field, 'Display:', display, 'Input:', input); // Debug

          if (!display || !input) {
              console.error('Display or input not found for field:', field);
              return;
          }

          // Store original value
          if (!originalValues[field]) {
              originalValues[field] = input.value;
          }

          // Toggle display/input
          display.style.display = 'none';
          input.style.display = 'block';
          input.focus();

          // Show action buttons
          if (formActions) {
              formActions.style.display = 'block';
          }

      });
  });

  // Cancel editing
  if (cancelBtn) {
      cancelBtn.addEventListener('click', function() {
          console.log('Cancel clicked'); // Debug
          
          // Restore all fields
          document.querySelectorAll('.field-input').forEach(input => {
              const field = input.getAttribute('data-field');
              const display = document.querySelector(`.field-display[data-field="${field}"]`);
              
              // Restore original value
              if (originalValues[field] !== undefined) {
                  input.value = originalValues[field];
              }

              input.style.display = 'none';
              if (display) {
                  display.style.display = 'block';
              }
          });

          if (formActions) {
              formActions.style.display = 'none';
          }
          originalValues = {};
      });
  }
});
/**
 * Admin Dashboard JavaScript
 * Based on Educenter Template
 */

// Toggle Sidebar
function toggleSidebar() {
  const sidebar = document.getElementById('adminSidebar');
  const content = document.getElementById('adminContent');
  const footer = document.getElementById('adminFooter');
  
  sidebar.classList.toggle('collapsed');
  content.classList.toggle('expanded');
  
  if (sidebar.classList.contains('collapsed')) {
    footer.style.marginLeft = '70px';
  } else {
    footer.style.marginLeft = '260px';
  }
}

// Toggle Mobile Sidebar
function toggleMobileSidebar() {
  const sidebar = document.getElementById('adminSidebar');
  sidebar.classList.toggle('mobile-open');
}

// Show Section
function showSection(sectionName) {
  // Hide all sections
  const sections = document.querySelectorAll('.content-section');
  sections.forEach(section => {
    section.style.display = 'none';
  });

  // Show selected section
  const selectedSection = document.getElementById('section-' + sectionName);
  if (selectedSection) {
    selectedSection.style.display = 'block';
  }

  // Update active menu item
  const menuItems = document.querySelectorAll('.sidebar-menu a');
  menuItems.forEach(item => {
    item.classList.remove('active');
  });
  event.target.closest('a').classList.add('active');

  // Close mobile sidebar after selection
  if (window.innerWidth <= 768) {
    document.getElementById('adminSidebar').classList.remove('mobile-open');
  }
}

// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
  // Registrations Chart
  const registrationsCtx = document.getElementById('registrationsChart');
  if (registrationsCtx) {
    new Chart(registrationsCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
        datasets: [{
          label: 'Inscriptions',
          data: [120, 190, 150, 250, 200, 280],
          borderColor: '#ffbc3b',
          backgroundColor: 'rgba(255, 188, 59, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  }
});

// Search Functionality
document.getElementById('userSearch')?.addEventListener('input', function(e) {
  const searchTerm = e.target.value.toLowerCase();
  const tableRows = document.querySelectorAll('#section-users tbody tr');
  
  tableRows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchTerm) ? '' : 'none';
  });
});

// Filter Buttons
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});

// Search Functions
function searchUsers() {
  const searchTerm = document.getElementById('userSearch').value.toLowerCase();
  console.log('Searching users for:', searchTerm);
}

function filterUsers(filter) {
  console.log('Filtering users by:', filter);
  document.querySelectorAll('#section-users .filter-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  event.target.classList.add('active');
}



//backoffice quiz JS 
/**
 * Filter Quiz by Type
 */
function filterQuiz(type) {
    const quizItems = document.querySelectorAll('.quiz-item');
    const buttons = document.querySelectorAll('.btn-group .btn');
    
    // Update active button - find the clicked button
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.toLowerCase().includes(type) || (type === 'all' && btn.textContent === 'Tous')) {
            btn.classList.add('active');
        }
    });
    
    // Filter items
    quizItems.forEach(item => {
        if (type === 'all') {
            item.style.display = 'block';
        } else {
            const itemType = item.dataset.type.toLowerCase();
            if (itemType === type.toLowerCase()) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        }
    });
}

/**
 * Smooth scroll for quiz items
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to quiz ranking
    const rankingContainer = document.querySelector('.quiz-ranking');
    if (rankingContainer) {
        rankingContainer.style.scrollBehavior = 'smooth';
    }
    
    // Add smooth scrolling to quiz list
    const quizListContainer = document.querySelector('.quiz-list-container');
    if (quizListContainer) {
        quizListContainer.style.scrollBehavior = 'smooth';
    }
});

// ========================================
// COURSE INLINE EDITING - SIMPLE VERSION
// (Same pattern as user profile editing)
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ===== COURSE INLINE EDITING =====
    const courseEditToggles = document.querySelectorAll('.course-edit-toggle');
    
    courseEditToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const courseId = this.dataset.courseId;
            const field = this.dataset.field;
            
            // Hide display, show input for this specific field
            const display = document.querySelector(`.course-field-display[data-course-id="${courseId}"][data-field="${field}"]`);
            const input = document.querySelector(`.course-field-input[data-course-id="${courseId}"][data-field="${field}"]`);
            
            if (display && input) {
                display.style.display = 'none';
                input.style.display = 'block';
                input.focus();
                
                // Show form actions for this course
                const actions = document.querySelector(`.course-form-actions[data-course-id="${courseId}"]`);
                if (actions) {
                    actions.style.display = 'block';
                }
            }
        });
    });

    // Cancel course edit
    const courseCancelBtns = document.querySelectorAll('.course-cancel-edit');
    courseCancelBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const courseId = this.dataset.courseId;
            
            // Hide all inputs, show all displays for this course
            document.querySelectorAll(`.course-field-input[data-course-id="${courseId}"]`).forEach(input => {
                input.style.display = 'none';
            });
            document.querySelectorAll(`.course-field-display[data-course-id="${courseId}"]`).forEach(display => {
                display.style.display = 'block';
            });
            
            // Hide form actions
            const actions = document.querySelector(`.course-form-actions[data-course-id="${courseId}"]`);
            if (actions) {
                actions.style.display = 'none';
            }
        });
    });
});
(function ($) {
    'use strict';

    // Sticky Menu
    $(window).scroll(function () {
        if ($('header').offset().top > 10) {
            $('.top-header').addClass('hide');
            $('.navigation').addClass('nav-bg');
        } else {
            $('.top-header').removeClass('hide');
            $('.navigation').removeClass('nav-bg');
        }
    });

    // Background-images
    $('[data-background]').each(function () {
        $(this).css({
            'background-image': 'url(' + $(this).data('background') + ')'
        });
    });

    //Hero Slider
    $('.hero-slider').slick({
        autoplay: true,
        autoplaySpeed: 7500,
        pauseOnFocus: false,
        pauseOnHover: false,
        infinite: true,
        arrows: true,
        fade: true,
        prevArrow: '<button type=\'button\' class=\'prevArrow\'><i class=\'ti-angle-left\'></i></button>',
        nextArrow: '<button type=\'button\' class=\'nextArrow\'><i class=\'ti-angle-right\'></i></button>',
        dots: true
    });
    $('.hero-slider').slickAnimation();

    // venobox popup
    $(document).ready(function(){
        $('.venobox').venobox(); 
    });

    
    // mixitup filter
    var containerEl = document.querySelector('[data-ref~="mixitup-container"]');
    var mixer;
    if (containerEl) {
        mixer = mixitup(containerEl, {
            selectors: {
                target: '[data-ref~="mixitup-target"]'
            }
        });
    }

    //  Count Up
    function counter() {
        var oTop;
        if ($('.count').length !== 0) {
            oTop = $('.count').offset().top - window.innerHeight;
        }
        if ($(window).scrollTop() > oTop) {
            $('.count').each(function () {
                var $this = $(this),
                    countTo = $this.attr('data-count');
                $({
                    countNum: $this.text()
                }).animate({
                    countNum: countTo
                }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function () {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function () {
                        $this.text(this.countNum);
                    }
                });
            });
        }
    }
    $(window).on('scroll', function () {
        counter();
    });

})(jQuery);



//User.html JS 
// Student Dashboard Custom JavaScript

// Display current year in footer
document.addEventListener('DOMContentLoaded', function() {
  var CurrentYear = new Date().getFullYear();
  var yearElements = document.querySelectorAll('.copyright script');
  yearElements.forEach(function(element) {
    var yearSpan = document.createElement('span');
    yearSpan.textContent = CurrentYear;
    element.parentNode.insertBefore(yearSpan, element);
    element.remove();
  });
});

//sign up page custom js
// Update the hidden accountType field when radio buttons change
document.addEventListener('DOMContentLoaded', function() {
    const studentCard = document.getElementById('studentCard');
    const teacherCard = document.getElementById('teacherCard');
    const radioButtons = document.querySelectorAll('input[name="accountType"]');
    const hiddenAccountType = document.querySelector('select[id*="accountType"]') || 
                              document.querySelector('input[id*="accountType"]');
    const teacherFields = document.getElementById('teacherFields');

    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            // Update visual state
            studentCard.classList.remove('active');
            teacherCard.classList.remove('active');
            
            if (this.value === 'student') {
                studentCard.classList.add('active');
                if (hiddenAccountType) hiddenAccountType.value = 'ROLE_STUDENT';
                // Hide teacher fields
                if (teacherFields) teacherFields.style.display = 'none';
            } else {
                teacherCard.classList.add('active');
                if (hiddenAccountType) hiddenAccountType.value = 'ROLE_TEACHER';
                // Show teacher fields
                if (teacherFields) teacherFields.style.display = 'block';
            }
        });
    });

    // Initialize on page load - check which radio is selected
    const checkedRadio = document.querySelector('input[name="accountType"]:checked');
    if (checkedRadio && checkedRadio.value === 'teacher') {
        if (teacherFields) teacherFields.style.display = 'block';
    }
});
//User page custom js
// Student Dashboard Custom JavaScript

// Display current year in footer
document.addEventListener('DOMContentLoaded', function() {
  var CurrentYear = new Date().getFullYear();
  var yearElements = document.querySelectorAll('.copyright script');
  yearElements.forEach(function(element) {
    var yearSpan = document.createElement('span');
    yearSpan.textContent = CurrentYear;
    element.parentNode.insertBefore(yearSpan, element);
    element.remove();
  });
});



// Login Page Custom JavaScript

document.addEventListener('DOMContentLoaded', function() {
  
  // Display current year in footer
  var CurrentYear = new Date().getFullYear();
  var yearSpan = document.getElementById('currentYear');
  if (yearSpan) {
    yearSpan.textContent = CurrentYear;
  }

  // Password Toggle
  const togglePassword = document.getElementById('togglePassword');
  if (togglePassword) {
    togglePassword.addEventListener('click', function () {
      const password = document.getElementById('password');
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('ti-eye');
      this.classList.toggle('ti-eye-off');
    });
  }

});


// Login Page Custom JavaScript
// Formateur Profile Page Custom JavaScript

document.addEventListener('DOMContentLoaded', function() {
  
  // Display current year in footer
  var CurrentYear = new Date().getFullYear();
  var yearSpan = document.getElementById('currentYear');
  if (yearSpan) {
    yearSpan.textContent = CurrentYear;
  }

  // Inline editing for profile fields
  const editToggles = document.querySelectorAll('.edit-icon');
  const formActions = document.getElementById('formActions');
  const cancelBtn = document.getElementById('cancelEdit');
  const profileForm = document.getElementById('profileForm');
  let originalValues = {};

  console.log('Edit toggles found:', editToggles.length); // Debug
  console.log('Form actions:', formActions); // Debug

  editToggles.forEach(toggle => {
      toggle.addEventListener('click', function(e) {
          e.preventDefault();
          console.log('Edit clicked'); // Debug
          
          const field = this.getAttribute('data-field');
          const display = document.querySelector(`.field-display[data-field="${field}"]`);
          const input = document.querySelector(`.field-input[data-field="${field}"]`);

          console.log('Field:', field, 'Display:', display, 'Input:', input); // Debug

          if (!display || !input) {
              console.error('Display or input not found for field:', field);
              return;
          }

          // Store original value
          if (!originalValues[field]) {
              originalValues[field] = input.value;
          }

          // Toggle display/input
          display.style.display = 'none';
          input.style.display = 'block';
          input.focus();

          // Show action buttons
          if (formActions) {
              formActions.style.display = 'block';
          }

      });
  });

  // Cancel editing
  if (cancelBtn) {
      cancelBtn.addEventListener('click', function() {
          console.log('Cancel clicked'); // Debug
          
          // Restore all fields
          document.querySelectorAll('.field-input').forEach(input => {
              const field = input.getAttribute('data-field');
              const display = document.querySelector(`.field-display[data-field="${field}"]`);
              
              // Restore original value
              if (originalValues[field] !== undefined) {
                  input.value = originalValues[field];
              }

              input.style.display = 'none';
              if (display) {
                  display.style.display = 'block';
              }
          });

          if (formActions) {
              formActions.style.display = 'none';
          }
          originalValues = {};
      });
  }
});
/**
 * Admin Dashboard JavaScript
 * Based on Educenter Template
 */

// Toggle Sidebar
function toggleSidebar() {
  const sidebar = document.getElementById('adminSidebar');
  const content = document.getElementById('adminContent');
  const footer = document.getElementById('adminFooter');
  
  sidebar.classList.toggle('collapsed');
  content.classList.toggle('expanded');
  
  if (sidebar.classList.contains('collapsed')) {
    footer.style.marginLeft = '70px';
  } else {
    footer.style.marginLeft = '260px';
  }
}

// Toggle Mobile Sidebar
function toggleMobileSidebar() {
  const sidebar = document.getElementById('adminSidebar');
  sidebar.classList.toggle('mobile-open');
}

// Show Section
function showSection(sectionName) {
  // Hide all sections
  const sections = document.querySelectorAll('.content-section');
  sections.forEach(section => {
    section.style.display = 'none';
  });

  // Show selected section
  const selectedSection = document.getElementById('section-' + sectionName);
  if (selectedSection) {
    selectedSection.style.display = 'block';
  }

  // Update active menu item
  const menuItems = document.querySelectorAll('.sidebar-menu a');
  menuItems.forEach(item => {
    item.classList.remove('active');
  });
  event.target.closest('a').classList.add('active');

  // Close mobile sidebar after selection
  if (window.innerWidth <= 768) {
    document.getElementById('adminSidebar').classList.remove('mobile-open');
  }
}

// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
  // Registrations Chart
  const registrationsCtx = document.getElementById('registrationsChart');
  if (registrationsCtx) {
    new Chart(registrationsCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
        datasets: [{
          label: 'Inscriptions',
          data: [120, 190, 150, 250, 200, 280],
          borderColor: '#ffbc3b',
          backgroundColor: 'rgba(255, 188, 59, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  }
});

// Search Functionality
document.getElementById('userSearch')?.addEventListener('input', function(e) {
  const searchTerm = e.target.value.toLowerCase();
  const tableRows = document.querySelectorAll('#section-users tbody tr');
  
  tableRows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchTerm) ? '' : 'none';
  });
});

// Filter Buttons
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});

// Search Functions
function searchUsers() {
  const searchTerm = document.getElementById('userSearch').value.toLowerCase();
  console.log('Searching users for:', searchTerm);
}

function filterUsers(filter) {
  console.log('Filtering users by:', filter);
  document.querySelectorAll('#section-users .filter-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  event.target.classList.add('active');
}



//backoffice quiz JS 
/**
 * Filter Quiz by Type
 */
function filterQuiz(type) {
    const quizItems = document.querySelectorAll('.quiz-item');
    const buttons = document.querySelectorAll('.btn-group .btn');
    
    // Update active button - find the clicked button
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.toLowerCase().includes(type) || (type === 'all' && btn.textContent === 'Tous')) {
            btn.classList.add('active');
        }
    });
    
    // Filter items
    quizItems.forEach(item => {
        if (type === 'all') {
            item.style.display = 'block';
        } else {
            const itemType = item.dataset.type.toLowerCase();
            if (itemType === type.toLowerCase()) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        }
    });
}

/**
 * Smooth scroll for quiz items
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to quiz ranking
    const rankingContainer = document.querySelector('.quiz-ranking');
    if (rankingContainer) {
        rankingContainer.style.scrollBehavior = 'smooth';
    }
    
    // Add smooth scrolling to quiz list
    const quizListContainer = document.querySelector('.quiz-list-container');
    if (quizListContainer) {
        quizListContainer.style.scrollBehavior = 'smooth';
    }
});

// ========================================
// COURSE INLINE EDITING - SIMPLE VERSION
// (Same pattern as user profile editing)
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ===== COURSE INLINE EDITING =====
    const courseEditToggles = document.querySelectorAll('.course-edit-toggle');
    
    courseEditToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const courseId = this.dataset.courseId;
            const field = this.dataset.field;
            
            // Hide display, show input for this specific field
            const display = document.querySelector(`.course-field-display[data-course-id="${courseId}"][data-field="${field}"]`);
            const input = document.querySelector(`.course-field-input[data-course-id="${courseId}"][data-field="${field}"]`);
            
            if (display && input) {
                display.style.display = 'none';
                input.style.display = 'block';
                input.focus();
                
                // Show form actions for this course
                const actions = document.querySelector(`.course-form-actions[data-course-id="${courseId}"]`);
                if (actions) {
                    actions.style.display = 'block';
                }
            }
        });
    });

    // Cancel course edit
    const courseCancelBtns = document.querySelectorAll('.course-cancel-edit');
    courseCancelBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const courseId = this.dataset.courseId;
            
            // Hide all inputs, show all displays for this course
            document.querySelectorAll(`.course-field-input[data-course-id="${courseId}"]`).forEach(input => {
                input.style.display = 'none';
            });
            document.querySelectorAll(`.course-field-display[data-course-id="${courseId}"]`).forEach(display => {
                display.style.display = 'block';
            });
            
            // Hide form actions
            const actions = document.querySelector(`.course-form-actions[data-course-id="${courseId}"]`);
            if (actions) {
                actions.style.display = 'none';
            }
        });
    });
});

// ==================== QUIZ INLINE EDITING ====================

document.addEventListener('DOMContentLoaded', function() {
    
    // Handle quiz edit toggle
    const quizEditToggles = document.querySelectorAll('.quiz-edit-toggle');
    
    quizEditToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const quizId = this.getAttribute('data-quiz-id');
            const field = this.getAttribute('data-field');
            
            // Hide display, show input
            const displayElement = document.querySelector(`.quiz-field-display[data-quiz-id="${quizId}"][data-field="${field}"]`);
            const inputElement = document.querySelector(`.quiz-field-input[data-quiz-id="${quizId}"][data-field="${field}"]`);
            
            if (displayElement && inputElement) {
                displayElement.style.display = 'none';
                inputElement.style.display = 'block';
                inputElement.focus();
                
                // Show form actions
                const formActions = document.querySelector(`.quiz-form-actions[data-quiz-id="${quizId}"]`);
                if (formActions) {
                    formActions.style.display = 'block';
                }
            }
        });
    });
    
    // Handle quiz cancel edit
    const quizCancelButtons = document.querySelectorAll('.quiz-cancel-edit');
    
    quizCancelButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const quizId = this.getAttribute('data-quiz-id');
            
            // Hide all inputs, show all displays for this quiz
            const displays = document.querySelectorAll(`.quiz-field-display[data-quiz-id="${quizId}"]`);
            const inputs = document.querySelectorAll(`.quiz-field-input[data-quiz-id="${quizId}"]`);
            
            displays.forEach(function(display) {
                display.style.display = 'block';
            });
            
            inputs.forEach(function(input) {
                input.style.display = 'none';
                // Reset to original value
                const field = input.getAttribute('data-field');
                const display = document.querySelector(`.quiz-field-display[data-quiz-id="${quizId}"][data-field="${field}"]`);
                if (display && input.tagName === 'INPUT') {
                    input.value = display.textContent.trim().replace('%', '').replace(' min', '');
                } else if (display && input.tagName === 'TEXTAREA') {
                    input.value = display.textContent.trim();
                } else if (input.tagName === 'SELECT') {
                    const displayText = display.textContent.trim();
                    for (let i = 0; i < input.options.length; i++) {
                        if (input.options[i].text === displayText) {
                            input.selectedIndex = i;
                            break;
                        }
                    }
                }
            });
            
            // Hide form actions
            const formActions = document.querySelector(`.quiz-form-actions[data-quiz-id="${quizId}"]`);
            if (formActions) {
                formActions.style.display = 'none';
            }
        });
    });
    
    // Handle quiz form submission
    const quizForms = document.querySelectorAll('.quiz-form');
    
    quizForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            // Let the form submit normally
            // After successful submission, the page will reload
        });
    });
});

// ==================== COURSE INLINE EDITING (EXISTING) ====================

document.addEventListener('DOMContentLoaded', function() {
    
    // Handle course edit toggle
    const courseEditToggles = document.querySelectorAll('.course-edit-toggle');
    
    courseEditToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const courseId = this.getAttribute('data-course-id');
            const field = this.getAttribute('data-field');
            
            // Hide display, show input
            const displayElement = document.querySelector(`.course-field-display[data-course-id="${courseId}"][data-field="${field}"]`);
            const inputElement = document.querySelector(`.course-field-input[data-course-id="${courseId}"][data-field="${field}"]`);
            
            if (displayElement && inputElement) {
                displayElement.style.display = 'none';
                inputElement.style.display = 'block';
                inputElement.focus();
                
                // Show form actions
                const formActions = document.querySelector(`.course-form-actions[data-course-id="${courseId}"]`);
                if (formActions) {
                    formActions.style.display = 'block';
                }
            }
        });
    });
    
    // Handle course cancel edit
    const courseCancelButtons = document.querySelectorAll('.course-cancel-edit');
    
    courseCancelButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const courseId = this.getAttribute('data-course-id');
            
            // Hide all inputs, show all displays for this course
            const displays = document.querySelectorAll(`.course-field-display[data-course-id="${courseId}"]`);
            const inputs = document.querySelectorAll(`.course-field-input[data-course-id="${courseId}"]`);
            
            displays.forEach(function(display) {
                display.style.display = 'block';
            });
            
            inputs.forEach(function(input) {
                input.style.display = 'none';
                // Reset to original value
                const field = input.getAttribute('data-field');
                const display = document.querySelector(`.course-field-display[data-course-id="${courseId}"][data-field="${field}"]`);
                if (display && input.tagName === 'INPUT') {
                    input.value = display.textContent.trim();
                } else if (display && input.tagName === 'TEXTAREA') {
                    input.value = display.textContent.trim();
                } else if (input.tagName === 'SELECT') {
                    const displayText = display.textContent.trim();
                    for (let i = 0; i < input.options.length; i++) {
                        if (input.options[i].text === displayText) {
                            input.selectedIndex = i;
                            break;
                        }
                    }
                }
            });
            
            // Hide form actions
            const formActions = document.querySelector(`.course-form-actions[data-course-id="${courseId}"]`);
            if (formActions) {
                formActions.style.display = 'none';
            }
        });
    });
});

// ==================== USER PROFILE INLINE EDITING (EXISTING) ====================

document.addEventListener('DOMContentLoaded', function() {
    
    // Handle profile field edit toggle
    const editToggles = document.querySelectorAll('.edit-icon');
    
    editToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const field = this.getAttribute('data-field');
            
            // Hide display, show input
            const displayElement = document.querySelector(`.field-display[data-field="${field}"]`);
            const inputElement = document.querySelector(`.field-input[data-field="${field}"]`);
            
            if (displayElement && inputElement) {
                displayElement.style.display = 'none';
                inputElement.style.display = 'block';
                inputElement.focus();
                
                // Show form actions
                const formActions = document.getElementById('formActions');
                if (formActions) {
                    formActions.style.display = 'block';
                }
            }
        });
    });
    
    // Handle cancel edit
    const cancelButton = document.getElementById('cancelEdit');
    
    if (cancelButton) {
        cancelButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Hide all inputs, show all displays
            const displays = document.querySelectorAll('.field-display');
            const inputs = document.querySelectorAll('.field-input');
            
            displays.forEach(function(display) {
                display.style.display = 'block';
            });
            
            inputs.forEach(function(input) {
                input.style.display = 'none';
                // Reset to original value
                const field = input.getAttribute('data-field');
                const display = document.querySelector(`.field-display[data-field="${field}"]`);
                if (display && input.tagName === 'INPUT') {
                    if (field === 'password') {
                        input.value = '';
                    } else {
                        input.value = display.textContent.trim();
                    }
                } else if (display && input.tagName === 'TEXTAREA') {
                    input.value = display.textContent.trim();
                }
            });
            
            // Hide form actions
            const formActions = document.getElementById('formActions');
            if (formActions) {
                formActions.style.display = 'none';
            }
        });
    }
});

// ==================== QUIZ INLINE EDITING & DELETE (UPDATED) ====================
document.addEventListener('DOMContentLoaded', function() {
    
    // ==================== EDIT FUNCTIONALITY ====================
    const quizEditToggles = document.querySelectorAll('.quiz-edit-toggle');
    
    quizEditToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const quizId = this.getAttribute('data-quiz-id');
            
            // Hide display, show input for this quiz row
            const displayElements = document.querySelectorAll(`.quiz-field-display[data-quiz-id="${quizId}"]`);
            const inputElements = document.querySelectorAll(`.quiz-field-input[data-quiz-id="${quizId}"]`);
            
            displayElements.forEach(function(display) {
                display.style.display = 'none';
            });
            
            inputElements.forEach(function(input) {
                input.style.display = 'inline-block';
            });
            
            // Hide edit button, show save/cancel buttons
            this.style.display = 'none';
            document.querySelector(`.quiz-save-btn[data-quiz-id="${quizId}"]`).style.display = 'inline-block';
            document.querySelector(`.quiz-cancel-btn[data-quiz-id="${quizId}"]`).style.display = 'inline-block';
        });
    });
    
    // ==================== SAVE FUNCTIONALITY (FIXED) ====================
    const quizSaveButtons = document.querySelectorAll('.quiz-save-btn');
    
    quizSaveButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const quizId = this.getAttribute('data-quiz-id');
            const form = document.querySelector(`#quiz-edit-form-${quizId}`);
            
            if (!form) {
                console.error('Form not found for quiz ID:', quizId);
                return;
            }
            
            // Get all input fields and update corresponding form fields
            const inputs = document.querySelectorAll(`.quiz-field-input[data-quiz-id="${quizId}"]`);
            
            inputs.forEach(function(input) {
                const fieldName = input.getAttribute('data-field');
                const formField = form.querySelector(`[name="quiz[${fieldName}]"]`);
                
                if (formField) {
                    formField.value = input.value;
                    console.log(`Updated ${fieldName}:`, input.value);
                }
            });
            
            // Submit the form
            console.log('Submitting form for quiz:', quizId);
            form.submit();
        });
    });
    
    // ==================== CANCEL FUNCTIONALITY ====================
    const quizCancelButtons = document.querySelectorAll('.quiz-cancel-btn');
    
    quizCancelButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const quizId = this.getAttribute('data-quiz-id');
            
            // Reset inputs to original values
            const displayElements = document.querySelectorAll(`.quiz-field-display[data-quiz-id="${quizId}"]`);
            const inputElements = document.querySelectorAll(`.quiz-field-input[data-quiz-id="${quizId}"]`);
            
            inputElements.forEach(function(input) {
                const field = input.getAttribute('data-field');
                const display = document.querySelector(`.quiz-field-display[data-quiz-id="${quizId}"][data-field="${field}"]`);
                if (display) {
                    input.value = display.textContent.trim();
                }
                input.style.display = 'none';
            });
            
            displayElements.forEach(function(display) {
                display.style.display = 'inline';
            });
            
            // Show edit button, hide save/cancel buttons
            document.querySelector(`.quiz-edit-toggle[data-quiz-id="${quizId}"]`).style.display = 'inline-block';
            this.style.display = 'none';
            document.querySelector(`.quiz-save-btn[data-quiz-id="${quizId}"]`).style.display = 'none';
        });
    });
    
    // ==================== DELETE FUNCTIONALITY ====================
    const quizDeleteButtons = document.querySelectorAll('.quiz-delete-btn');
    
    quizDeleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const quizId = this.getAttribute('data-quiz-id');
            const quizTitle = this.getAttribute('data-quiz-title') || 'ce quiz';
            
            // Show confirmation dialog
            if (confirm(`Êtes-vous sûr de vouloir supprimer "${quizTitle}" ? Cette action est irréversible.`)) {
                // Find and submit the delete form
                const deleteForm = document.querySelector(`#quiz-delete-form-${quizId}`);
                if (deleteForm) {
                    deleteForm.submit();
                } else {
                    console.error('Delete form not found for quiz ID:', quizId);
                }
            }
        });
    });
});



// Quiz inline editing functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Quiz edit toggle
    document.querySelectorAll('.quiz-edit-toggle').forEach(function(editBtn) {
        editBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const quizId = this.getAttribute('data-quiz-id');
            const field = this.getAttribute('data-field');
            
            // Hide display, show input
            const display = document.querySelector(`.quiz-field-display[data-quiz-id="${quizId}"][data-field="${field}"]`);
            const input = document.querySelector(`.quiz-field-input[data-quiz-id="${quizId}"][data-field="${field}"]`);
            
            if (display && input) {
                display.style.display = 'none';
                input.style.display = 'block';
                input.focus();
                
                // Show form actions
                const formActions = document.querySelector(`.quiz-form-actions[data-quiz-id="${quizId}"]`);
                if (formActions) {
                    formActions.style.display = 'block';
                }
            }
        });
    });
    
    // Quiz cancel edit
    document.querySelectorAll('.quiz-cancel-edit').forEach(function(cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            const quizId = this.getAttribute('data-quiz-id');
            
            // Hide all inputs, show all displays for this quiz
            document.querySelectorAll(`.quiz-field-input[data-quiz-id="${quizId}"]`).forEach(function(input) {
                input.style.display = 'none';
                // Reset to original value
                input.value = input.defaultValue;
            });
            
            document.querySelectorAll(`.quiz-field-display[data-quiz-id="${quizId}"]`).forEach(function(display) {
                display.style.display = 'block';
            });
            
            // Hide form actions
            const formActions = document.querySelector(`.quiz-form-actions[data-quiz-id="${quizId}"]`);
            if (formActions) {
                formActions.style.display = 'none';
            }
        });
    });
});