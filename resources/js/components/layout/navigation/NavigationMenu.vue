<template>
    <ul class="space-y-2 font-medium">
        <IconMenuItem
            :route="{ name: 'dashboard' }"
            :title="$t('Home')"
            icon="house-line"
        />
        <!-- System Settings Menu -->
        <template v-if="auth.hasPermission('show system menu')">
            <template v-if="auth.hasPermission('show system settings menu')">
                <ModuleMenuItem
                    module="settings"
                    :moduleName="$t('Settings')"
                    icon="gear"
                >
                    <template #items>
                        <PlainMenuItem
                            v-if="auth.hasPermission('list users')"
                            :route="{ name: 'settings.users.list' }"
                            baseRoute="settings.users"
                            :title="$t('Users')"
                        />
                    </template>
                </ModuleMenuItem>
            </template>
        </template>
        <!-- Academic Menu -->
        <template v-if="auth.hasPermission('show academic menu')">
            <ModuleMenuItem
                module="academic"
                :moduleName="$t('Academic')"
                icon="graduation-cap"
            >
                <template #items>
                    <template v-if="auth.hasPermission('show academic settings menu')">
                        <!-- Academic Settings Menu-->
                        <SubmoduleMenuItem
                            submodule="settings"
                            :submoduleName="$t('Settings')"
                            icon="sliders"
                        >
                            <template #items>
                                <PlainMenuItem
                                    v-if="auth.hasPermission('list language levels')"
                                    :route="{ name: 'academic.settings.language-levels.list' }"
                                    baseRoute="academic.settings.language-levels"
                                    :title="$t('Language Levels')"
                                />
                            </template>
                        </SubmoduleMenuItem>
                        <!-- Academic Classes Menu-->
                        <SubmoduleMenuItem
                            submodule="classes"
                            :submoduleName="$t('Classes')"
                            icon="chalkboard-teacher"
                        >
                            <template #items>
                                <PlainMenuItem
                                    v-if="auth.hasPermission('view class schedules')"
                                    :route="{ name: 'academic.classes.class-schedules.list' }"
                                    baseRoute="academic.classes.class-schedules"
                                    :title="$t('Class Schedules')"
                                />
                            </template>
                        </SubmoduleMenuItem>
                    </template>
                </template>
            </ModuleMenuItem>
        </template>
    </ul>
</template>
<script setup>
import IconMenuItem from './IconMenuItem.vue';
import ModuleMenuItem from './ModuleMenuItem.vue';
import SubmoduleMenuItem from './SubmoduleMenuItem.vue';
import PlainMenuItem from './PlainMenuItem.vue';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from 'vue-i18n';

const auth = useAuthStore();
const { t } = useI18n();
</script>