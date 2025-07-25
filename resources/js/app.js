import "./bootstrap";

// DATASTAR Error Handling for Development
// Shows Laravel error pages in modal overlay during Datastar requests
document.addEventListener("DOMContentLoaded", function () {
    //Enhanced fetch interception for better error capture
    const originalFetch = window.fetch;
    window.fetch = async function (url, options = {}) {
        try {
            const response = await originalFetch.apply(this, arguments);

            // Check if this is a Datastar request and has an error status
            const isDatastarRequest =
                options.headers?.["Datastar-Request"] ||
                (options.headers && options.headers["Datastar-Request"]);

            if (
                !response.ok &&
                (isDatastarRequest ||
                    (typeof url === "string" &&
                        url.includes("/") &&
                        response.status >= 400))
            ) {
                // Clone response to read content without consuming it
                const responseClone = response.clone();
                const html = await responseClone.text();

                // Only show modal for HTML error responses (likely Laravel error pages)
                const contentType = response.headers.get("content-type") || "";
                if (
                    (contentType.includes("text/html") &&
                        html.includes("<!DOCTYPE")) ||
                    html.includes("<html") ||
                    response.status >= 400
                ) {
                    showErrorModal(html);
                }
            }

            return response;
        } catch (error) {
            console.error("🚨 Fetch error:", error);
            showErrorModal(`
                <h1>Network Error</h1>
                <p><strong>URL:</strong> ${url}</p>
                <p><strong>Error:</strong> ${error.message}</p>
                <p><strong>Type:</strong> ${error.name || "Unknown"}</p>
            `);
            throw error; // Re-throw to maintain normal error handling
        }
    };

    // Modal creation function (same as your HTMX version)
    function showErrorModal(html) {
        // Remove existing modal if present
        const existingModal = document.getElementById("datastar-error-modal");
        if (existingModal) {
            existingModal.remove();
        }

        // Create full-screen error modal
        const modal = document.createElement("div");
        modal.id = "datastar-error-modal";
        Object.assign(modal.style, {
            position: "fixed",
            inset: "0",
            background: "rgba(0,0,0,0.8)",
            overflow: "auto",
            zIndex: "9999",
            display: "flex",
            alignItems: "flex-start",
            justifyContent: "center",
            padding: "2rem",
        });

        // Error content container
        const inner = document.createElement("div");
        Object.assign(inner.style, {
            background: "#f5f5f5",
            padding: "1.5rem",
            borderRadius: "8px",
            boxShadow: "0 4px 20px rgba(0,0,0,0.3)",
            maxWidth: "90vw",
            maxHeight: "90vh",
            width: "1000px",
            position: "relative",
            overflow: "auto",
        });

        // Close button
        const closeBtn = document.createElement("button");
        closeBtn.innerHTML = "&times;";
        Object.assign(closeBtn.style, {
            position: "absolute",
            top: "0.5rem",
            right: "0.5rem",
            fontSize: "2rem",
            lineHeight: "1",
            background: "#ff4444",
            color: "white",
            border: "none",
            borderRadius: "50%",
            width: "30px",
            height: "30px",
            cursor: "pointer",
            zIndex: "1",
        });
        closeBtn.addEventListener("click", () => modal.remove());

        // Keyboard close (ESC key)
        const handleKeydown = (e) => {
            if (e.key === "Escape") {
                modal.remove();
                document.removeEventListener("keydown", handleKeydown);
            }
        };
        document.addEventListener("keydown", handleKeydown);

        // Assemble modal
        inner.appendChild(closeBtn);
        const content = document.createElement("div");
        content.innerHTML = html;
        inner.appendChild(content);
        modal.appendChild(inner);
        document.body.appendChild(modal);

        // Focus the modal for accessibility
        modal.focus();
    }
});

window.showToast = function (type, message) {
    // Define toast configurations
    const toastConfigs = {
        success: {
            bgColor: "bg-green-50",
            borderColor: "border-green-200",
            textColor: "text-green-800",
            iconColor: "text-green-500",
            icon: `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                     <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                   </svg>`,
        },
        error: {
            bgColor: "bg-red-50",
            borderColor: "border-red-200",
            textColor: "text-red-800",
            iconColor: "text-red-500",
            icon: `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                     <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                   </svg>`,
        },
        info: {
            bgColor: "bg-blue-50",
            borderColor: "border-blue-200",
            textColor: "text-blue-800",
            iconColor: "text-blue-500",
            icon: `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                     <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                   </svg>`,
        },
        warning: {
            bgColor: "bg-orange-50",
            borderColor: "border-orange-200",
            textColor: "text-orange-800",
            iconColor: "text-orange-500",
            icon: `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                     <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                   </svg>`,
        },
    };

    const config = toastConfigs[type];
    if (!config) {
        console.error(
            "Invalid toast type. Use: success, error, info, or warning"
        );
        return;
    }

    // Create toast element
    const toastId =
        "toast-" + Date.now() + Math.random().toString(36).substr(2, 9);
    const toastElement = document.createElement("div");
    toastElement.id = toastId;
    toastElement.className = `
        ${config.bgColor} ${config.borderColor} ${config.textColor}
        border-l-4 p-4 rounded-lg shadow-lg
        transform translate-x-full transition-all duration-300 ease-out
        flex items-start space-x-3
    `;

    toastElement.innerHTML = `
        <div class="${config.iconColor} flex-shrink-0 mt-0.5">
            ${config.icon}
        </div>
        <div class="flex-1">
            <p class="text-sm font-medium leading-5">${message}</p>
        </div>
        <button onclick="removeToast('${toastId}')" class="flex-shrink-0 ml-4 text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    `;

    // Add to container
    const container = document.getElementById("toast-notifier");
    if (!container) {
        console.error(
            'Toast container not found. Make sure you have a div with id="toast-notifier"'
        );
        return;
    }

    container.appendChild(toastElement);

    // Animate in
    setTimeout(() => {
        toastElement.classList.remove("translate-x-full");
        toastElement.classList.add("translate-x-0");
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        removeToast(toastId);
    }, 5000);
};

function removeToast(toastId) {
    const toastElement = document.getElementById(toastId);
    if (toastElement) {
        toastElement.classList.add("translate-x-full", "opacity-0");
        setTimeout(() => {
            if (toastElement.parentNode) {
                toastElement.parentNode.removeChild(toastElement);
            }
        }, 300);
    }
}
