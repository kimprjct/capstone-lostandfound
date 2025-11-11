<!-- Notification Bell Component -->
<div class="dropdown notification-bell-container position-relative">
    <button class="btn btn-link position-relative notification-bell-btn" id="notificationBell" type="button">
        <i class="fas fa-bell notification-bell-icon"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" id="notificationCount" style="display: none;">
            0
        </span>
    </button>
    
    <!-- Notification Dropdown -->
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 400px; max-height: 500px; overflow-y: auto;">
        <div class="dropdown-header d-flex justify-content-between align-items-center px-4 py-3" style="border-bottom: 1px solid #e9ecef;">
            <h6 class="mb-0 fw-bold text-dark">Notifications</h6>
            <button class="btn btn-sm btn-outline-primary px-3" id="markAllRead" style="white-space: nowrap; font-size: 0.8rem;">Mark All Read</button>
        </div>
        
        <!-- Notifications List -->
        <div id="notificationsList" class="px-0">
            <div class="text-center p-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2 text-muted">Loading notifications...</span>
            </div>
        </div>
        
    </div>
</div>

<style>
/* Notification Bell Styling */
.notification-bell-container {
    margin: 0 0.5rem;
    position: relative;
}

.notification-bell-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
    position: relative;
    overflow: visible;
    cursor: pointer;
    outline: none;
}

.notification-bell-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}

.notification-bell-btn:focus {
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
    outline: none;
}

.notification-bell-icon {
    color: white !important;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.notification-bell-btn:hover .notification-bell-icon {
    transform: scale(1.1);
    animation: ring 0.5s ease-in-out;
}

.notification-badge {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%) !important;
    border: 2px solid white;
    border-radius: 50% !important;
    font-size: 0.7rem;
    font-weight: 600;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
}

@keyframes ring {
    0% { transform: rotate(0deg); }
    25% { transform: rotate(-15deg); }
    75% { transform: rotate(15deg); }
    100% { transform: rotate(0deg); }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Dropdown Styling */
.notification-dropdown {
    border: 1px solid #dee2e6;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.75rem;
    padding: 0;
    overflow: hidden;
    position: absolute;
    top: calc(100% + 10px);
    right: -155px;
    z-index: 1050;
    margin-top: 0.5rem;
    display: none;
}

.notification-dropdown.show {
    display: block !important;
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.notification-dropdown {
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}

.notification-item {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.notification-item:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.notification-item.unread {
    background-color: #e3f2fd !important;
    border-left: 4px solid #2196f3 !important;
    position: relative;
    box-shadow: inset 0 0 0 1px rgba(33, 150, 243, 0.2) !important;
}

.notification-item.unread::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, rgba(33, 150, 243, 0.15) 0%, transparent 100%);
    pointer-events: none;
    z-index: 1;
}

.notification-item.read {
    background-color: #ffffff !important;
    border-left: none !important;
    box-shadow: none !important;
}

.notification-item.read::before {
    display: none !important;
}

.notification-item.force-read {
    background-color: #ffffff !important;
    border-left: none !important;
    box-shadow: none !important;
    position: relative !important;
}

.notification-item.force-read::before {
    display: none !important;
    content: none !important;
}

.notification-item.force-read::after {
    display: none !important;
    content: none !important;
}

.notification-item.read:hover {
    background-color: #f8f9fa !important;
}

.notification-item::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    opacity: 0;
    transition: opacity 0.2s ease;
}

.notification-item:hover::after {
    opacity: 1;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-title {
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
    line-height: 1.3;
}

.notification-message {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.notification-time {
    color: #adb5bd;
    font-size: 0.8rem;
    font-weight: 500;
}

.notification-priority {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    margin-right: 0.75rem;
    margin-top: 0.5rem;
    flex-shrink: 0;
}

.priority-urgent { 
    background-color: #dc3545; 
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
}
.priority-high { 
    background-color: #fd7e14; 
    box-shadow: 0 0 0 2px rgba(253, 126, 20, 0.2);
}
.priority-normal { 
    background-color: #0d6efd; 
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.2);
}
.priority-low { 
    background-color: #6c757d; 
    box-shadow: 0 0 0 2px rgba(108, 117, 125, 0.2);
}

/* Button improvements */
#markAllRead {
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s ease;
    white-space: nowrap;
}

#markAllRead:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
}

/* Loading state improvements */
.notification-dropdown .spinner-border {
    width: 1.5rem;
    height: 1.5rem;
}

/* Error state improvements */
.notification-dropdown .text-center {
    padding: 2rem 1rem;
}

.notification-dropdown .text-center i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.7;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBell = document.getElementById('notificationBell');
    const notificationCount = document.getElementById('notificationCount');
    const notificationsList = document.getElementById('notificationsList');
    const markAllReadBtn = document.getElementById('markAllRead');
    const viewAllNotifications = document.getElementById('viewAllNotifications');

    // Global variables
    let refreshInterval;
    let globalRefreshInterval;
    let isDropdownOpen = false;
    let lastNotificationCount = 0;
    let currentNotifications = [];

    // Initialize everything
    initializeNotificationBell();

    function initializeNotificationBell() {
        // Fetch initial notifications and count
        fetchUnreadCount();
        fetchNotifications();
        
        // Start global auto-refresh (every 3 seconds for real-time updates)
        startGlobalAutoRefresh();
        
        // Set up event listeners
        setupEventListeners();
    }

    function setupEventListeners() {
        // Bell click handler
        notificationBell.addEventListener('click', handleBellClick);
        
        // Mark all as read handler
        markAllReadBtn.addEventListener('click', handleMarkAllRead);
        
        // View all notifications handler
        viewAllNotifications.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/notifications';
        });
        
        // Click outside to close dropdown
        document.addEventListener('click', handleClickOutside);
    }

    function handleBellClick(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const dropdownMenu = document.querySelector('.notification-dropdown');
        
        if (isDropdownOpen) {
            closeDropdown(dropdownMenu);
        } else {
            openDropdown(dropdownMenu);
        }
    }

    function openDropdown(dropdownMenu) {
        dropdownMenu.style.display = 'block';
        dropdownMenu.style.backgroundColor = '#ffffff';
        dropdownMenu.style.border = '2px solid #007bff';
        dropdownMenu.style.position = 'absolute';
        dropdownMenu.style.top = 'calc(100% + 10px)';
        dropdownMenu.style.right = '-155px';
        dropdownMenu.style.zIndex = '9999';
        dropdownMenu.classList.add('show');
        isDropdownOpen = true;
        
        // Fetch fresh notifications when opening
        fetchNotifications();
        
        // Start local refresh (every 2 seconds when open)
        startLocalAutoRefresh();
    }

    function closeDropdown(dropdownMenu) {
        dropdownMenu.style.display = 'none';
        dropdownMenu.classList.remove('show');
        isDropdownOpen = false;
        stopLocalAutoRefresh();
    }

    function handleClickOutside(event) {
        const dropdownMenu = document.querySelector('.notification-dropdown');
        if (!notificationBell.contains(event.target) && !dropdownMenu.contains(event.target)) {
            if (isDropdownOpen) {
                closeDropdown(dropdownMenu);
            }
        }
    }

    // Fetch notifications
    function fetchNotifications() {
        const userRole = '{{ auth()->user()->role }}';
        let apiEndpoint = '/api/notifications?per_page=10';
        
        if (userRole === 'tenant') {
            apiEndpoint = '/api/notifications/organization?per_page=10';
        } else if (userRole === 'admin') {
            apiEndpoint = '/api/notifications/admin?per_page=10';
        }
        
        fetch(apiEndpoint, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Notification API Response:', data);
            if (data.success) {
                const notifications = data.data.data || [];
                console.log('Notifications received:', notifications);
                currentNotifications = notifications;
                displayNotifications(notifications);
                updateUnreadCount(notifications);
                
                // Check for new notifications
                const currentUnreadCount = notifications.filter(n => !n.is_read).length;
                console.log('Current unread count:', currentUnreadCount);
                if (currentUnreadCount > lastNotificationCount && lastNotificationCount > 0) {
                    showNewNotificationAlert();
                }
                lastNotificationCount = currentUnreadCount;
            } else {
                throw new Error(data.message || 'Failed to fetch notifications');
            }
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
            showErrorState();
        });
    }

    // Display notifications
    function displayNotifications(notifications) {
        if (notifications.length === 0) {
            notificationsList.innerHTML = `
                <div class="text-center p-4">
                    <i class="fas fa-bell-slash text-muted mb-3" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    <div class="text-muted mb-2" style="font-weight: 500;">No notifications yet</div>
                    <small class="text-muted">You're all caught up!</small>
                </div>
            `;
            return;
        }

        const html = notifications.map(notification => {
            const isUnread = !notification.is_read;
            return `
            <div class="notification-item ${isUnread ? 'unread' : 'read'}" 
                 data-notification-id="${notification.id}"
                 data-notification-type="${notification.type}"
                 data-notification-data='${JSON.stringify(notification.data || {})}'
                 data-is-read="${notification.is_read}">
                <div class="d-flex align-items-start p-3">
                    <span class="notification-priority priority-${notification.priority} me-2 mt-1"></span>
                    <div class="flex-grow-1">
                        <div class="notification-title fw-medium">${notification.title}</div>
                        <div class="notification-message text-muted small">${notification.message}</div>
                        <div class="notification-time text-muted" style="font-size: 0.75rem;">${formatTime(notification.created_at)}</div>
                    </div>
                    ${isUnread ? '<span class="badge bg-primary rounded-pill" style="font-size: 0.6rem;">New</span>' : ''}
                </div>
            </div>
        `;
        }).join('');

        notificationsList.innerHTML = html;
        
        // Add click event listeners
        addNotificationClickListeners();
    }

    function addNotificationClickListeners() {
        const notificationItems = notificationsList.querySelectorAll('.notification-item');
        console.log('Adding click listeners to', notificationItems.length, 'notifications');
        
        notificationItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const notificationId = this.getAttribute('data-notification-id');
                const notificationType = this.getAttribute('data-notification-type');
                const notificationData = JSON.parse(this.getAttribute('data-notification-data') || '{}');
                const isRead = this.getAttribute('data-is-read') === 'true';
                
                console.log('Notification clicked:', {
                    id: notificationId,
                    type: notificationType,
                    isRead: isRead,
                    element: this
                });
                
                if (!isRead) {
                    // Mark as read first, then navigate
                    handleNotificationClick(notificationId, notificationType, notificationData, this);
                } else {
                    // If already read, just navigate
                    navigateToNotification(notificationType, notificationData);
                }
            });
        });
    }

    function handleNotificationClick(notificationId, type, data, element) {
        console.log('Handling notification click for ID:', notificationId);
        
        // Mark as read first, then navigate
        markAsRead(notificationId, element, () => {
            // Navigate after successful mark as read
            navigateToNotification(type, data);
        });
    }

    function markAsRead(notificationId, element, callback) {
        console.log('Marking notification as read:', notificationId);
        console.log('Element before update:', element);
        
        // IMMEDIATELY update UI for instant feedback - ONLY for this specific notification
        console.log('Immediately updating UI for notification ID:', notificationId);
        
        // Remove blue highlight immediately - ONLY for this element
        element.classList.remove('unread');
        element.classList.add('read');
        element.setAttribute('data-is-read', 'true');
        
        // Force remove all blue styling - ONLY for this element
        element.style.setProperty('background-color', '#ffffff', 'important');
        element.style.setProperty('border-left', 'none', 'important');
        element.style.setProperty('box-shadow', 'none', 'important');
        
        // Remove the "New" badge immediately - ONLY from this element
        const badge = element.querySelector('.badge');
        if (badge) {
            console.log('Removing badge from notification ID:', notificationId);
            badge.remove();
        }
        
        // Update current notifications array immediately - ONLY for this specific notification
        const notificationIndex = currentNotifications.findIndex(n => n.id == notificationId);
        if (notificationIndex !== -1) {
            currentNotifications[notificationIndex].is_read = true;
            console.log('Updated notification in array for ID:', notificationId, currentNotifications[notificationIndex]);
        }
        
        // Update the counter immediately - this will only affect the count, not other notifications
        updateUnreadCount(currentNotifications);
        
        // Then make the API call
        fetch(`/api/notifications/${notificationId}/read`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('Mark as read response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Mark as read response data:', data);
            if (data.success) {
                console.log('Successfully marked as read in database');
                
                // Call callback if provided
                if (callback && typeof callback === 'function') {
                    callback();
                }
                
                // Only refresh unread count, not all notifications to avoid affecting other notifications
                setTimeout(() => {
                    fetchUnreadCount();
                }, 300);
                
            } else {
                console.error('Failed to mark notification as read for ID:', notificationId, data.message);
                // Revert UI changes if API failed - ONLY for this specific notification
                element.classList.remove('read');
                element.classList.add('unread');
                element.setAttribute('data-is-read', 'false');
                element.style.removeProperty('background-color');
                element.style.removeProperty('border-left');
                element.style.removeProperty('box-shadow');
                
                // Restore badge - ONLY for this specific notification
                if (notificationIndex !== -1) {
                    currentNotifications[notificationIndex].is_read = false;
                    // Re-add the badge to this specific element
                    const badgeContainer = element.querySelector('.d-flex');
                    if (badgeContainer && !badgeContainer.querySelector('.badge')) {
                        badgeContainer.innerHTML += '<span class="badge bg-primary rounded-pill" style="font-size: 0.6rem;">New</span>';
                    }
                }
                updateUnreadCount(currentNotifications);
                
                // Still call callback even if failed
                if (callback && typeof callback === 'function') {
                    callback();
                }
            }
        })
        .catch(error => {
            console.error('Error marking notification as read for ID:', notificationId, error);
            
            // Revert UI changes if API failed - ONLY for this specific notification
            element.classList.remove('read');
            element.classList.add('unread');
            element.setAttribute('data-is-read', 'false');
            element.style.removeProperty('background-color');
            element.style.removeProperty('border-left');
            element.style.removeProperty('box-shadow');
            
            // Restore badge - ONLY for this specific notification
            if (notificationIndex !== -1) {
                currentNotifications[notificationIndex].is_read = false;
                // Re-add the badge to this specific element
                const badgeContainer = element.querySelector('.d-flex');
                if (badgeContainer && !badgeContainer.querySelector('.badge')) {
                    badgeContainer.innerHTML += '<span class="badge bg-primary rounded-pill" style="font-size: 0.6rem;">New</span>';
                }
            }
            updateUnreadCount(currentNotifications);
            
            // Still call callback even if failed
            if (callback && typeof callback === 'function') {
                callback();
            }
        });
    }

    function navigateToNotification(type, data) {
        const userRole = '{{ auth()->user()->role }}';
        let targetUrl = null;
        
        try {
            switch (type) {
                case 'new_item':
                    if (data.item_type === 'lost') {
                        targetUrl = userRole === 'admin' ? 
                            `/admin/lost-items/${data.item_id}` : 
                            `/tenant/lost-items/${data.item_id}/manage`;
                    } else if (data.item_type === 'found') {
                        targetUrl = userRole === 'admin' ? 
                            `/admin/found-items/${data.item_id}` : 
                            `/tenant/found-items/${data.item_id}/manage`;
                    }
                    break;
                    
                case 'new_claim':
                    if (data.claim_id) {
                        targetUrl = userRole === 'admin' ? 
                            `/admin/claims/${data.claim_id}` : 
                            `/tenant/claims/${data.claim_id}/review`;
                    }
                    break;
                    
                default:
                    targetUrl = userRole === 'admin' ? '/admin/notifications' : '/tenant/notifications';
                    break;
            }
            
            if (targetUrl) {
                window.location.href = targetUrl;
            }
            
        } catch (error) {
            console.error('Error navigating to notification:', error);
        }
    }

    function handleMarkAllRead() {
        console.log('Marking all notifications as read...');
        
        // IMMEDIATELY update UI for instant feedback
        console.log('Immediately updating all notifications UI...');
        
        // Update all notifications to read state immediately
        const notificationItems = notificationsList.querySelectorAll('.notification-item');
        notificationItems.forEach(item => {
            item.classList.remove('unread');
            item.classList.add('read');
            item.setAttribute('data-is-read', 'true');
            
            // Force remove blue highlight styles
            item.style.setProperty('background-color', '#ffffff', 'important');
            item.style.setProperty('border-left', 'none', 'important');
            item.style.setProperty('box-shadow', 'none', 'important');
            
            const badge = item.querySelector('.badge');
            if (badge) {
                badge.remove();
            }
        });
        
        // Update current notifications array immediately
        currentNotifications.forEach(notification => {
            notification.is_read = true;
        });
        
        // Hide the counter immediately
        notificationCount.style.display = 'none';
        
        // Then make the API call
        fetch('/api/notifications/mark-all-read', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Mark all read response:', data);
            if (data.success) {
                console.log('Successfully marked all as read in database');
                
                // Only refresh unread count, not all notifications to avoid affecting other notifications
                setTimeout(() => {
                    fetchUnreadCount();
                }, 300);
            } else {
                console.error('Failed to mark all as read:', data.message);
                // Revert UI changes if API failed
                notificationItems.forEach(item => {
                    item.classList.remove('read');
                    item.classList.add('unread');
                    item.setAttribute('data-is-read', 'false');
                    item.style.removeProperty('background-color');
                    item.style.removeProperty('border-left');
                    item.style.removeProperty('box-shadow');
                });
                
                currentNotifications.forEach(notification => {
                    notification.is_read = false;
                });
                updateUnreadCount(currentNotifications);
            }
        })
        .catch(error => {
            console.error('Error marking all as read:', error);
            
            // Revert UI changes if API failed
            notificationItems.forEach(item => {
                item.classList.remove('read');
                item.classList.add('unread');
                item.setAttribute('data-is-read', 'false');
                item.style.removeProperty('background-color');
                item.style.removeProperty('border-left');
                item.style.removeProperty('box-shadow');
            });
            
            currentNotifications.forEach(notification => {
                notification.is_read = false;
            });
            updateUnreadCount(currentNotifications);
        });
    }

    // Update unread count
    function updateUnreadCount(notifications) {
        const unreadCount = notifications.filter(n => !n.is_read).length;
        console.log('Updating unread count:', unreadCount, 'from', notifications.length, 'notifications');
        
        // Force update the count display immediately
        if (unreadCount > 0) {
            const displayCount = unreadCount > 99 ? '99+' : unreadCount.toString();
            notificationCount.textContent = displayCount;
            notificationCount.style.display = 'block';
            notificationCount.style.visibility = 'visible';
            console.log('Showing notification count:', displayCount);
        } else {
            notificationCount.style.display = 'none';
            notificationCount.style.visibility = 'hidden';
            console.log('Hiding notification count');
        }
        
        // Update the global variable
        lastNotificationCount = unreadCount;
        
        // Log the current state for debugging (only for the specific notification being updated)
        console.log('Current notification states:', notifications.map(n => ({
            id: n.id,
            is_read: n.is_read,
            title: n.title
        })));
        
        // Force a reflow to ensure the display change is applied
        notificationCount.offsetHeight;
    }

    // Fetch unread count only
    function fetchUnreadCount() {
        const userRole = '{{ auth()->user()->role }}';
        let apiEndpoint = '/api/notifications/unread-count';
        
        // Always use the unread-count endpoint for consistency
        fetch(apiEndpoint, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Unread count API Response:', data);
            if (data.success) {
                const unreadCount = data.count || 0;
                console.log('Unread count from API:', unreadCount);
                
                if (unreadCount > 0) {
                    notificationCount.textContent = unreadCount > 99 ? '99+' : unreadCount.toString();
                    notificationCount.style.display = 'block';
                    console.log('Showing notification count:', notificationCount.textContent);
                } else {
                    notificationCount.style.display = 'none';
                    console.log('Hiding notification count');
                }
                
                lastNotificationCount = unreadCount;
            } else {
                console.error('Failed to fetch unread count:', data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching unread count:', error);
        });
    }

    // Auto-refresh functions
    function startGlobalAutoRefresh() {
        if (globalRefreshInterval) clearInterval(globalRefreshInterval);
        globalRefreshInterval = setInterval(fetchUnreadCount, 10000); // Every 10 seconds
    }

    function startLocalAutoRefresh() {
        if (refreshInterval) clearInterval(refreshInterval);
        refreshInterval = setInterval(fetchNotifications, 5000); // Every 5 seconds when dropdown is open
    }

    function stopLocalAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }

    // Utility functions
    function formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Just now';
        if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
        if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
        return Math.floor(diff / 86400000) + 'd ago';
    }

    function showNewNotificationAlert() {
        notificationBell.style.animation = 'pulse 0.5s ease-in-out 3';
        setTimeout(() => {
            notificationBell.style.animation = '';
        }, 1500);
    }

    function showErrorState() {
        notificationsList.innerHTML = `
            <div class="text-center p-4">
                <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 2.5rem; opacity: 0.7;"></i>
                <div class="text-muted mb-2" style="font-weight: 500;">Unable to load notifications</div>
                <small class="text-muted">Please check your connection and try again</small>
            </div>
        `;
    }

    // Test function for debugging
    window.testNotificationBell = function() {
        console.log('Testing notification bell...');
        console.log('Current notifications:', currentNotifications);
        console.log('Is dropdown open:', isDropdownOpen);
        fetchNotifications();
        fetchUnreadCount();
    };
    
    // Test function to simulate clicking a notification
    window.testNotificationClick = function(notificationId) {
        console.log('Testing notification click for ID:', notificationId);
        const element = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (element) {
            console.log('Found notification element:', element);
            element.click();
        } else {
            console.log('Notification element not found');
        }
    };
    
    // Test function to create a test notification
    window.createTestNotification = function() {
        console.log('Creating test notification...');
        fetch('/api/test-notification', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Test notification response:', data);
            if (data.success) {
                alert('Test notification created! ID: ' + data.notification_id);
                // Refresh notifications
                setTimeout(() => {
                    fetchNotifications();
                    fetchUnreadCount();
                }, 1000);
            } else {
                alert('Failed to create test notification: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error creating test notification:', error);
            alert('Error creating test notification: ' + error.message);
        });
    };
    
    // Test function to check notifications in database
    window.checkNotificationsInDB = function() {
        console.log('Checking notifications in database...');
        fetch('/api/check-notifications', {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Database notifications:', data);
            if (data.success) {
                console.log('Total notifications in DB:', data.total_notifications);
                console.log('All notifications:', data.notifications);
            } else {
                console.error('Failed to check notifications:', data.message);
            }
        })
        .catch(error => {
            console.error('Error checking notifications:', error);
        });
    };
    
    // Test function to simulate the exact scenario: 2 notifications → click 1 → should show 1
    window.testNotificationScenario = function() {
        console.log('=== Testing Notification Scenario ===');
        console.log('Current notifications:', currentNotifications);
        
        const unreadNotifications = currentNotifications.filter(n => !n.is_read);
        console.log('Unread notifications:', unreadNotifications.length);
        
        if (unreadNotifications.length >= 2) {
            console.log('Found 2+ unread notifications, testing click on first one...');
            const firstUnread = unreadNotifications[0];
            console.log('Clicking notification ID:', firstUnread.id);
            
            const element = document.querySelector(`[data-notification-id="${firstUnread.id}"]`);
            if (element) {
                console.log('Found element, clicking...');
                element.click();
            } else {
                console.log('Element not found for notification ID:', firstUnread.id);
            }
        } else {
            console.log('Not enough unread notifications to test. Current unread count:', unreadNotifications.length);
        }
    };
});
</script>