@extends('layouts.tenantApp')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Notifications</h1>
            <p class="text-muted">Manage your organization's notifications</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="markAllReadBtn">
                <i class="fas fa-check-double me-2"></i>Mark All Read
            </button>
            <button class="btn btn-primary" id="refreshBtn">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Notifications</option>
                        <option value="unread">Unread Only</option>
                        <option value="read">Read Only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="priorityFilter" class="form-label">Priority</label>
                    <select class="form-select" id="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="normal">Normal</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="categoryFilter" class="form-label">Category</label>
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="item">Items</option>
                        <option value="claim">Claims</option>
                        <option value="system">System</option>
                        <option value="announcement">Announcements</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dateFilter" class="form-label">Date Range</label>
                    <select class="form-select" id="dateFilter">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button class="btn btn-primary" id="applyFiltersBtn">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                    <button class="btn btn-outline-secondary ms-2" id="clearFiltersBtn">
                        <i class="fas fa-times me-2"></i>Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Notifications</h5>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted" id="notificationCount">Loading...</span>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="viewMode" id="listView" autocomplete="off" checked>
                    <label class="btn btn-outline-secondary" for="listView">
                        <i class="fas fa-list"></i>
                    </label>
                    <input type="radio" class="btn-check" name="viewMode" id="gridView" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="gridView">
                        <i class="fas fa-th"></i>
                    </label>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Loading State -->
            <div id="loadingState" class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading notifications...</p>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="text-center p-5" style="display: none;">
                <i class="fas fa-bell-slash text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-muted">No notifications found</h5>
                <p class="text-muted">You're all caught up! Check back later for new notifications.</p>
            </div>

            <!-- Notifications List -->
            <div id="notificationsList" class="list-group list-group-flush">
                <!-- Notifications will be loaded here -->
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" class="d-flex justify-content-center p-3" style="display: none;">
                <nav aria-label="Notifications pagination">
                    <ul class="pagination mb-0" id="pagination">
                        <!-- Pagination will be loaded here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<style>
.notification-item {
    border-left: 4px solid transparent;
    transition: all 0.2s ease;
}

.notification-item:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
}

.notification-item.unread {
    background-color: #e3f2fd;
    border-left-color: #2196f3;
}

.notification-item.urgent {
    border-left-color: #dc3545;
}

.notification-item.high {
    border-left-color: #fd7e14;
}

.notification-item.normal {
    border-left-color: #0d6efd;
}

.notification-item.low {
    border-left-color: #6c757d;
}

.priority-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.category-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.notification-time {
    font-size: 0.8rem;
    color: #6c757d;
}

.notification-actions {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.notification-item:hover .notification-actions {
    opacity: 1;
}

.grid-view .notification-item {
    margin-bottom: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}

.grid-view .notification-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentFilters = new Object();
    let currentPage = 1;
    let totalPages = 1;
    let isLoading = false;

    // Elements
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const notificationsList = document.getElementById('notificationsList');
    const paginationContainer = document.getElementById('paginationContainer');
    const pagination = document.getElementById('pagination');
    const notificationCount = document.getElementById('notificationCount');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');

    // Filter elements
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const dateFilter = document.getElementById('dateFilter');

    // View mode elements
    const listView = document.getElementById('listView');
    const gridView = document.getElementById('gridView');

    // Initialize
    loadNotifications();
    setupEventListeners();

    function setupEventListeners() {
        // Filter events
        applyFiltersBtn.addEventListener('click', applyFilters);
        clearFiltersBtn.addEventListener('click', clearFilters);
        
        // Action events
        markAllReadBtn.addEventListener('click', markAllAsRead);
        refreshBtn.addEventListener('click', () => {
            currentPage = 1;
            loadNotifications();
        });

        // View mode events
        listView.addEventListener('change', () => {
            if (listView.checked) {
                notificationsList.classList.remove('grid-view');
            }
        });
        
        gridView.addEventListener('change', () => {
            if (gridView.checked) {
                notificationsList.classList.add('grid-view');
            }
        });
    }

    function applyFilters() {
        currentFilters = new Object();
        
        if (statusFilter.value) currentFilters.status = statusFilter.value;
        if (priorityFilter.value) currentFilters.priority = priorityFilter.value;
        if (categoryFilter.value) currentFilters.category = categoryFilter.value;
        if (dateFilter.value) currentFilters.date = dateFilter.value;
        
        currentPage = 1;
        loadNotifications();
    }

    function clearFilters() {
        statusFilter.value = '';
        priorityFilter.value = '';
        categoryFilter.value = '';
        dateFilter.value = '';
        currentFilters = new Object();
        currentPage = 1;
        loadNotifications();
    }

    async function loadNotifications() {
        if (isLoading) return;
        
        isLoading = true;
        showLoadingState();

        try {
            const params = new URLSearchParams({
                page: currentPage,
                per_page: 20,
                ...currentFilters
            });

            const response = await fetch(`/api/notifications/organization?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                displayNotifications(data.data.data || []);
                updatePagination(data.data);
                updateNotificationCount(data.data.total || 0);
            } else {
                throw new Error(data.message || 'Failed to load notifications');
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            showErrorState(error.message);
        } finally {
            isLoading = false;
        }
    }

    function displayNotifications(notifications) {
        if (notifications.length === 0) {
            showEmptyState();
            return;
        }

        hideLoadingState();
        hideEmptyState();

        const html = notifications.map(notification => `
            <div class="notification-item list-group-item ${!notification.is_read ? 'unread' : ''} ${notification.priority}" 
                 data-id="${notification.id}">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <div class="priority-indicator priority-${notification.priority}"></div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-bold">${notification.title}</h6>
                                <p class="mb-2 text-muted">${notification.message}</p>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="priority-badge badge bg-${getPriorityColor(notification.priority)}">
                                        ${notification.priority.toUpperCase()}
                                    </span>
                                    <span class="category-badge badge bg-secondary">
                                        ${notification.category.toUpperCase()}
                                    </span>
                                    <span class="notification-time">
                                        ${formatTime(notification.created_at)}
                                    </span>
                                </div>
                            </div>
                            <div class="notification-actions">
                                <div class="btn-group-vertical btn-group-sm">
                                    ${!notification.is_read ? 
                                        `<button class="btn btn-outline-primary btn-sm" onclick="markAsRead(${notification.id})">
                                            <i class="fas fa-check"></i>
                                        </button>` : ''
                                    }
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteNotification(${notification.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        notificationsList.innerHTML = html;
    }

    function updatePagination(data) {
        totalPages = data.last_page || 1;
        
        if (totalPages <= 1) {
            paginationContainer.style.display = 'none';
            return;
        }

        paginationContainer.style.display = 'flex';
        
        let paginationHtml = '';
        
        // Previous button
        paginationHtml += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
            </li>
        `;
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                </li>
            `;
        }
        
        // Next button
        paginationHtml += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
            </li>
        `;
        
        pagination.innerHTML = paginationHtml;
    }

    function changePage(page) {
        if (page < 1 || page > totalPages || page === currentPage) return;
        currentPage = page;
        loadNotifications();
    }

    function updateNotificationCount(total) {
        notificationCount.textContent = `${total} notification${total !== 1 ? 's' : ''}`;
    }

    function getPriorityColor(priority) {
        const colors = {
            'urgent': 'danger',
            'high': 'warning',
            'normal': 'primary',
            'low': 'secondary'
        };
        return colors[priority] || 'secondary';
    }

    function formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Just now';
        if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
        if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
        if (diff < 604800000) return Math.floor(diff / 86400000) + 'd ago';
        return date.toLocaleDateString();
    }

    function showLoadingState() {
        loadingState.style.display = 'block';
        emptyState.style.display = 'none';
        notificationsList.style.display = 'none';
    }

    function hideLoadingState() {
        loadingState.style.display = 'none';
        notificationsList.style.display = 'block';
    }

    function showEmptyState() {
        emptyState.style.display = 'block';
        loadingState.style.display = 'none';
        notificationsList.style.display = 'none';
    }

    function hideEmptyState() {
        emptyState.style.display = 'none';
    }

    function showErrorState(message) {
        notificationsList.innerHTML = `
            <div class="text-center p-5">
                <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-danger">Error Loading Notifications</h5>
                <p class="text-muted">${message}</p>
                <button class="btn btn-primary" onclick="loadNotifications()">
                    <i class="fas fa-retry me-2"></i>Try Again
                </button>
            </div>
        `;
        hideLoadingState();
    }

    // Global functions for inline onclick handlers
    window.markAsRead = async function(notificationId) {
        try {
            const response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('unread');
                    const markAsReadBtn = notificationItem.querySelector('.btn-outline-primary');
                    if (markAsReadBtn) {
                        markAsReadBtn.remove();
                    }
                }
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    window.deleteNotification = async function(notificationId) {
        if (!confirm('Are you sure you want to delete this notification?')) return;

        try {
            const response = await fetch(`/api/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.remove();
                }
            }
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    };

    window.changePage = changePage;

    async function markAllAsRead() {
        try {
            const response = await fetch('/api/notifications/mark-all-read', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                // Remove unread styling from all notifications
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    const markAsReadBtn = item.querySelector('.btn-outline-primary');
                    if (markAsReadBtn) {
                        markAsReadBtn.remove();
                    }
                });
            }
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    }
});
</script>
@endsection
