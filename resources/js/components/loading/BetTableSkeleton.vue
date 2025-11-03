<template>
    <div class="card-table table-responsive">
        <table class="table table-vcenter table-hover card-table">
            <thead>
                <tr>
                    <th v-for="i in 11" :key="i">
                        <div
                            class="placeholder bg-light rounded-pill"
                            style="height: 10px; width: 60%"
                        ></div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="i in 10" :key="'row-' + i">
                    <td class="text-start">
                        <div
                            class="placeholder bg-light rounded-pill"
                            :style="{ height: '14px', width: Math.random() * 30 + 60 + 'px' }"
                        ></div>
                    </td>
                    <td v-for="j in 9" :key="'col-' + j">
                        <div
                            class="placeholder bg-light rounded-pill"
                            :style="{ height: '14px', width: Math.random() * 20 + 40 + 'px' }"
                        ></div>
                    </td>
                    <td class="text-end">
                        <div
                            class="placeholder bg-light rounded-pill"
                            :style="{ height: '14px', width: Math.random() * 25 + 50 + 'px' }"
                        ></div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="d-flex justify-content-end p-3 border-top">
            <div
                class="placeholder bg-light rounded-pill me-3"
                style="height: 20px; width: 150px"
            ></div>
            <div class="placeholder bg-light rounded-pill" style="height: 20px; width: 250px"></div>
        </div>
    </div>
</template>

<style scoped>
/* Base animation for a light, fading pulse effect */
.placeholder {
    opacity: 0.5;
    animation: pulse-light 1.5s infinite ease-in-out;
}

@keyframes pulse-light {
    0% {
        opacity: 0.4;
    }
    50% {
        opacity: 0.7;
    }
    100% {
        opacity: 0.4;
    }
}

/* Customizing the placeholder background color */
.bg-light {
    background-color: #e9ecef !important; /* Lighter shade of grey */
}
</style>
