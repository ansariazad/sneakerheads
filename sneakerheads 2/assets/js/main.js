document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", () => {
      document.body.classList.toggle("mobile-menu-active")
    })
  }

  // Mobile dropdown toggle
  const dropdownToggles = document.querySelectorAll(".dropdown-toggle")
  dropdownToggles.forEach((toggle) => {
    toggle.addEventListener("click", function (e) {
      if (window.innerWidth <= 768) {
        e.preventDefault()
        this.classList.toggle("active")
      }
    })
  })

  // Product image gallery
  const mainImage = document.querySelector(".product-main-image img")
  const thumbnails = document.querySelectorAll(".product-thumbnail")

  if (mainImage && thumbnails.length > 0) {
    thumbnails.forEach((thumbnail) => {
      thumbnail.addEventListener("click", function () {
        const imgSrc = this.querySelector("img").getAttribute("src")
        mainImage.setAttribute("src", imgSrc)

        // Remove active class from all thumbnails
        thumbnails.forEach((thumb) => thumb.classList.remove("active"))

        // Add active class to clicked thumbnail
        this.classList.add("active")
      })
    })
  }

  // Form validation
  const forms = document.querySelectorAll("form[data-validate]")
  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      let isValid = true

      // Required fields
      const requiredFields = form.querySelectorAll("[required]")
      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false
          field.classList.add("error")

          // Add error message if it doesn't exist
          let errorMsg = field.nextElementSibling
          if (!errorMsg || !errorMsg.classList.contains("error-message")) {
            errorMsg = document.createElement("div")
            errorMsg.classList.add("error-message")
            errorMsg.textContent = "This field is required"
            field.parentNode.insertBefore(errorMsg, field.nextSibling)
          }
        } else {
          field.classList.remove("error")

          // Remove error message if it exists
          const errorMsg = field.nextElementSibling
          if (errorMsg && errorMsg.classList.contains("error-message")) {
            errorMsg.remove()
          }
        }
      })

      // Email validation
      const emailFields = form.querySelectorAll('input[type="email"]')
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

      emailFields.forEach((field) => {
        if (field.value.trim() && !emailRegex.test(field.value.trim())) {
          isValid = false
          field.classList.add("error")

          // Add error message if it doesn't exist
          let errorMsg = field.nextElementSibling
          if (!errorMsg || !errorMsg.classList.contains("error-message")) {
            errorMsg = document.createElement("div")
            errorMsg.classList.add("error-message")
            errorMsg.textContent = "Please enter a valid email address"
            field.parentNode.insertBefore(errorMsg, field.nextSibling)
          }
        }
      })

      // Password match validation
      const password = form.querySelector('input[name="password"]')
      const confirmPassword = form.querySelector('input[name="confirm_password"]')

      if (password && confirmPassword && password.value !== confirmPassword.value) {
        isValid = false
        confirmPassword.classList.add("error")

        // Add error message if it doesn't exist
        let errorMsg = confirmPassword.nextElementSibling
        if (!errorMsg || !errorMsg.classList.contains("error-message")) {
          errorMsg = document.createElement("div")
          errorMsg.classList.add("error-message")
          errorMsg.textContent = "Passwords do not match"
          confirmPassword.parentNode.insertBefore(errorMsg, confirmPassword.nextSibling)
        }
      }

      if (!isValid) {
        e.preventDefault()
      }
    })
  })

  // Add to cart animation
  const addToCartButtons = document.querySelectorAll(".add-to-cart")
  addToCartButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      if (!this.classList.contains("adding")) {
        this.classList.add("adding")
        const originalText = this.textContent
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...'

        setTimeout(() => {
          this.innerHTML = '<i class="fas fa-check"></i> Added!'

          setTimeout(() => {
            this.innerHTML = originalText
            this.classList.remove("adding")
          }, 1500)
        }, 1000)
      }
    })
  })

  // Payment method selection
  const paymentMethods = document.querySelectorAll(".payment-method")
  const codFeeElement = document.querySelector(".cod-fee")
  const totalElement = document.querySelector(".order-total")

  if (paymentMethods.length > 0 && codFeeElement && totalElement) {
    const originalTotal = Number.parseFloat(totalElement.getAttribute("data-total"))
    const codFee = Number.parseFloat(codFeeElement.getAttribute("data-fee"))

    paymentMethods.forEach((method) => {
      method.addEventListener("change", function () {
        if (this.value === "cod") {
          codFeeElement.textContent = "₹" + codFee.toFixed(2)
          codFeeElement.parentElement.style.display = "flex"
          totalElement.textContent = "₹" + (originalTotal + codFee).toFixed(2)
        } else {
          codFeeElement.parentElement.style.display = "none"
          totalElement.textContent = "₹" + originalTotal.toFixed(2)
        }
      })
    })
  }

  // UPI Payment simulation
  const upiPayButton = document.querySelector(".upi-pay-button")
  const paymentStatus = document.querySelector(".payment-status")

  if (upiPayButton && paymentStatus) {
    upiPayButton.addEventListener("click", function () {
      this.disabled = true
      paymentStatus.innerHTML =
        '<div class="payment-processing"><i class="fas fa-spinner fa-spin"></i> Processing payment...</div>'

      setTimeout(() => {
        paymentStatus.innerHTML =
          '<div class="payment-success"><i class="fas fa-check-circle"></i> Payment successful!</div>'

        // Redirect to order confirmation page after 2 seconds
        setTimeout(() => {
          window.location.href = this.getAttribute("data-redirect")
        }, 2000)
      }, 3000)
    })
  }

  // Dashboard sidebar dropdown
  const dashboardDropdowns = document.querySelectorAll(".dashboard-sidebar .has-dropdown > a")
  if (dashboardDropdowns.length > 0) {
    dashboardDropdowns.forEach((dropdown) => {
      dropdown.addEventListener("click", function (e) {
        e.preventDefault()
        this.parentElement.classList.toggle("open")
        const submenu = this.nextElementSibling
        if (submenu.style.maxHeight) {
          submenu.style.maxHeight = null
        } else {
          submenu.style.maxHeight = submenu.scrollHeight + "px"
        }
      })
    })
  }
})

// Add this code to the main.js file to handle the dropdown toggle behavior

document.addEventListener("DOMContentLoaded", () => {
  // User dropdown toggle
  const userDropdownToggle = document.querySelector(".user-dropdown .dropdown-toggle")
  const userDropdownMenu = document.querySelector(".user-dropdown .dropdown-menu")

  if (userDropdownToggle && userDropdownMenu) {
    // Open dropdown on click
    userDropdownToggle.addEventListener("click", (e) => {
      e.preventDefault()
      userDropdownMenu.style.display = userDropdownMenu.style.display === "block" ? "none" : "block"
    })

    // Close dropdown when mouse leaves the menu
    userDropdownMenu.addEventListener("mouseleave", () => {
      userDropdownMenu.style.display = "none"
    })

    // Prevent dropdown from closing when clicking inside the menu
    userDropdownMenu.addEventListener("click", (e) => {
      e.stopPropagation()
    })
  }

  // Mobile menu toggle
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
  const header = document.querySelector(".main-header")

  if (mobileMenuToggle && header) {
    mobileMenuToggle.addEventListener("click", () => {
      header.classList.toggle("mobile-menu-active")
    })
  }
})

// Contact Form Validation
document.addEventListener("DOMContentLoaded", () => {
  const contactForm = document.querySelector(".contact-form[data-validate]")

  if (contactForm) {
    contactForm.addEventListener("submit", (e) => {
      let hasError = false
      const requiredFields = contactForm.querySelectorAll("[required]")

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          field.style.borderColor = "#dc3545"
          hasError = true
        } else {
          field.style.borderColor = "#e1e1e1"
        }

        // Email validation
        if (field.type === "email" && field.value.trim()) {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
          if (!emailRegex.test(field.value.trim())) {
            field.style.borderColor = "#dc3545"
            hasError = true
          }
        }
      })

      if (hasError) {
        e.preventDefault()
        alert("Please fill in all required fields correctly.")
      }
    })

    // Reset field styling on input
    contactForm.querySelectorAll("input, textarea").forEach((field) => {
      field.addEventListener("input", function () {
        this.style.borderColor = "#e1e1e1"
      })
    })
  }

  // Mobile Menu Toggle
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
  const mobileNav = document.querySelector(".mobile-nav")
  const closeMenu = document.querySelector(".close-menu")

  if (mobileMenuToggle && mobileNav) {
    mobileMenuToggle.addEventListener("click", () => {
      mobileNav.classList.add("active")
      document.body.style.overflow = "hidden"
    })

    closeMenu.addEventListener("click", () => {
      mobileNav.classList.remove("active")
      document.body.style.overflow = ""
    })
  }
})

