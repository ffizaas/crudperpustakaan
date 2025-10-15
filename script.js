// Global variables
let currentFilter = "all";
let editingBookId = null;

// Initialize
document.addEventListener("DOMContentLoaded", function () {
  setupFormSubmit();
  setupSearch();
  setupFilterButtons();
});

// Setup form submit
function setupFormSubmit() {
  const form = document.getElementById("bookForm");
  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      submitForm();
    });
  }
}

// Setup search functionality
function setupSearch() {
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      searchBooks(this.value);
    });
  }
}

// Setup filter buttons
function setupFilterButtons() {
  const filterButtons = document.querySelectorAll(".filter-btn");
  filterButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      // Remove active class from all buttons
      filterButtons.forEach((b) => b.classList.remove("active"));
      // Add active class to clicked button
      this.classList.add("active");
      // Filter books
      const category = this.getAttribute("onclick").match(/'([^']+)'/)[1];
      filterBooks(category);
    });
  });
}

// Search books
function searchBooks(searchTerm) {
  const bookCards = document.querySelectorAll(".book-card");
  const searchLower = searchTerm.toLowerCase();

  bookCards.forEach((card) => {
    const title = card.querySelector(".book-title").textContent.toLowerCase();
    const author = card.querySelector(".book-author").textContent.toLowerCase();
    const category = card
      .querySelector(".book-category")
      .textContent.toLowerCase();

    if (
      title.includes(searchLower) ||
      author.includes(searchLower) ||
      category.includes(searchLower)
    ) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}

// Filter books by category
function filterBooks(category) {
  const bookCards = document.querySelectorAll(".book-card");

  bookCards.forEach((card) => {
    const bookCategory = card.getAttribute("data-category");

    if (category === "all" || bookCategory === category) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}

// Open modal for adding new book
function openModal() {
  document.getElementById("bookModal").style.display = "block";
  document.getElementById("modalTitle").textContent = "Tambah Buku Baru";
  document.getElementById("bookForm").reset();
  document.getElementById("bookId").value = "";
  editingBookId = null;
}

// Close modal
function closeModal() {
  document.getElementById("bookModal").style.display = "none";
  document.getElementById("bookForm").reset();
  editingBookId = null;
}

// Close modal when clicking outside
window.onclick = function (event) {
  const modal = document.getElementById("bookModal");
  if (event.target === modal) {
    closeModal();
  }
};

// Submit form data
function submitForm() {
  const form = document.getElementById("bookForm");
  const formData = new FormData(form);

  // Show loading state
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;
  submitBtn.innerHTML = '<i class="fas fa-spinner loading"></i> Menyimpan...';
  submitBtn.disabled = true;

  fetch("process_book.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((data) => {
      // Redirect will happen from PHP, so we just close modal
      closeModal();
      // Reload page to see changes
      setTimeout(() => {
        window.location.reload();
      }, 500);
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan saat menyimpan data.");
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    });
}

// Edit book - This would need to fetch data from server
function editBook(id) {
  // For now, we'll redirect to an edit page or show modal with fetched data
  // This is a simplified version - in real app, you'd fetch book data from server

  editingBookId = id;
  document.getElementById("modalTitle").textContent = "Edit Buku";
  document.getElementById("bookId").value = id;

  // Get book data from the card (simplified approach)
  const bookCard = document.querySelector(`.book-card[data-book-id="${id}"]`);
  if (bookCard) {
    document.getElementById("judul").value =
      bookCard.querySelector(".book-title").textContent;
    document.getElementById("pengarang").value = bookCard
      .querySelector(".book-author")
      .textContent.replace("Oleh: ", "");
    document.getElementById("kategori").value =
      bookCard.querySelector(".book-category").textContent;

    // For other fields, you'd need to fetch from server or store in data attributes
  }

  document.getElementById("bookModal").style.display = "block";
}

// Delete book
function deleteBook(id) {
  if (!confirm("Apakah Anda yakin ingin menghapus buku ini?")) {
    return;
  }

  // Show loading state
  const deleteBtn = event.target;
  const originalText = deleteBtn.innerHTML;
  deleteBtn.innerHTML = '<i class="fas fa-spinner loading"></i> Menghapus...';
  deleteBtn.disabled = true;

  const formData = new FormData();
  formData.append("action", "delete");
  formData.append("bookId", id);

  fetch("process_book.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((data) => {
      // Reload page to see changes
      window.location.reload();
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan saat menghapus data.");
      deleteBtn.innerHTML = originalText;
      deleteBtn.disabled = false;
    });
}

// Show notification
function showNotification(message, type = "success") {
  // Remove existing notifications
  const existingNotifications = document.querySelectorAll(".notification");
  existingNotifications.forEach((notification) => notification.remove());

  // Create new notification
  const notification = document.createElement("div");
  notification.className = `notification ${type}`;
  notification.textContent = message;
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1001;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    `;

  if (type === "success") {
    notification.style.background = "#10b981";
  } else {
    notification.style.background = "#ef4444";
  }

  document.body.appendChild(notification);

  // Auto remove after 5 seconds
  setTimeout(() => {
    notification.remove();
  }, 5000);
}

// Check for URL parameters to show notifications
document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const success = urlParams.get("success");
  const error = urlParams.get("error");

  if (success) {
    showNotification(success, "success");
  }

  if (error) {
    showNotification(error, "error");
  }
});

// Enhanced search with debouncing
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Apply debouncing to search
const debouncedSearch = debounce(function (searchTerm) {
  searchBooks(searchTerm);
}, 300);

// Update search event listener to use debounced version
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      debouncedSearch(this.value);
    });
  }
});

// Keyboard shortcuts
document.addEventListener("keydown", function (e) {
  // Ctrl + N untuk tambah buku baru
  if (e.ctrlKey && e.key === "n") {
    e.preventDefault();
    openModal();
  }

  // Escape untuk tutup modal
  if (e.key === "Escape") {
    closeModal();
  }
});

// Export books data (optional feature)
function exportBooks() {
  const books = document.querySelectorAll(".book-card");
  const exportData = [];

  books.forEach((card) => {
    if (card.style.display !== "none") {
      exportData.push({
        judul: card.querySelector(".book-title").textContent,
        pengarang: card
          .querySelector(".book-author")
          .textContent.replace("Oleh: ", ""),
        kategori: card.querySelector(".book-category").textContent,
        stok: card.querySelector(".book-stock").textContent,
      });
    }
  });

  const dataStr = JSON.stringify(exportData, null, 2);
  const dataBlob = new Blob([dataStr], { type: "application/json" });

  const link = document.createElement("a");
  link.href = URL.createObjectURL(dataBlob);
  link.download = "data-buku.json";
  link.click();
}

// Print books list
function printBooks() {
  window.print();
}
