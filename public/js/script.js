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
  const editToggles = document.querySelectorAll('.edit-toggle');
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

          // Change icon to check
          this.innerHTML = '<i class="ti-check"></i>';
          this.classList.remove('edit-toggle');
          this.classList.add('save-toggle');
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

          // Reset icons
          document.querySelectorAll('.save-toggle').forEach(icon => {
              icon.innerHTML = '<i class="ti-pencil"></i>';
              icon.classList.remove('save-toggle');
              icon.classList.add('edit-toggle');
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