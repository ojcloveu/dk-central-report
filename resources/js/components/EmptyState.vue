<script setup>
/*
 * Define the props this component accepts
 */
const props = defineProps({
    title: {
        type: String,
        default: 'No records found',
    },
    subtitle: {
        type: String,
        default: "Try adjusting your search or filter to find what you're looking for.",
    },
    // The action to perform when the refresh button is clicked
    refreshAction: {
        type: Function,
        required: false,
    },
    // Flag to control button visibility
    showRefreshButton: {
        type: Boolean,
        default: true,
    },
});

/*
 * Wrapper function to call the refreshAction prop
 */
const handleRefresh = () => {
    if (props.refreshAction) {
        props.refreshAction();
    }
};
</script>

<template>
    <div class="card-body text-center py-5">
        <div class="empty">
            <div class="empty-icon">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="icon"
                >
                    <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                    <path
                        d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"
                    ></path>
                    <path d="M9 17l1.5 -1.5"></path>
                    <path d="M14 12l-3 3"></path>
                </svg>
            </div>

            <p class="empty-title">{{ title }}</p>
            <p class="empty-subtitle text-muted">{{ subtitle }}</p>

            <div v-if="showRefreshButton" class="empty-action">
                <button class="btn btn-primary" @click="handleRefresh">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="icon me-1"
                    >
                        <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
                        <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.empty-icon {
    width: 5rem;
    height: 5rem;
    border-radius: 50%;
    background: #f1f3f5;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    position: relative;
}

.empty-icon::before {
    content: '';
    position: absolute;
    inset: -0.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);
    opacity: 0.5;
    z-index: -1;
}

.empty-icon .icon {
    width: 2.5rem;
    height: 2.5rem;
}

.empty-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.empty-subtitle {
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
</style>
