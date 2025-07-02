<template>
    <v-layout>
        <v-app class="flex h-screen flex-col app-container" theme="dark">
            <v-main class="flex overflow-hidden py-0 main-container !flex-1">
                <router-view v-slot="{ Component }">
                    <transition name="scale" mode="out-in">
                        <component :is="Component" class="flex flex-col flex-1" />
                    </transition>
                </router-view>
            </v-main>
        </v-app>
    </v-layout>
</template>
