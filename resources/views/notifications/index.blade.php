<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Notifications") }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div id="notifications-container">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadNotifications();

            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        });

        function loadNotifications() {
            fetch("/notifications")
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById("notifications-container");
                    container.innerHTML = "";

                    if (data.notifications.data.length === 0) {
                        container.innerHTML = "<p class=\"text-gray-500 dark:text-gray-400\">No notifications yet.</p>";
                        return;
                    }

                    data.notifications.data.forEach(notification => {
                        const notificationElement = createNotificationElement(notification);
                        container.appendChild(notificationElement);
                    });
                })
                .catch(error => console.error("Error loading notifications:", error));
        }

        function createNotificationElement(notification) {
            const div = document.createElement("div");
            div.className = `p-4 border-b border-gray-200 dark:border-gray-700 ${!notification.read_at ? "bg-blue-50 dark:bg-blue-900/20" : ""}`;

            div.innerHTML = `
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm text-gray-600 dark:text-gray-300">${notification.message}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">${new Date(notification.created_at).toLocaleString()}</p>
                    </div>
                    <div class="flex space-x-2">
                        ${!notification.read_at ? `<button onclick="markAsRead(${notification.id})" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">Mark Read</button>` : ""}
                        <button onclick="deleteNotification(${notification.id})" class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Delete</button>
                    </div>
                </div>
            `;

            return div;
        }

        function markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/read`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content"),
                    "Content-Type": "application/json"
                }
            })
            .then(() => loadNotifications())
            .catch(error => console.error("Error marking notification as read:", error));
        }

        function deleteNotification(notificationId) {
            if (!confirm("Are you sure you want to delete this notification?")) return;

            fetch(`/notifications/${notificationId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content"),
                    "Content-Type": "application/json"
                }
            })
            .then(() => loadNotifications())
            .catch(error => console.error("Error deleting notification:", error));
        }
    </script>
</x-app-layout>
