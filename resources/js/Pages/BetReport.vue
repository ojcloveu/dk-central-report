<template>
    <div>
        <h1>Welcome to Report!</h1>
        <p>Data from Laravel: {{ message }}</p>
        <p>User: {{ user.name }}</p>
    </div>
</template>

<script setup>
// Props automatically passed from Laravel
defineProps({
    message: String,
    user: Object,
});
</script>
