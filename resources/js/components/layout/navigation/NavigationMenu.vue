<template>
    <ul class="space-y-2 font-medium">
        <IconMenuItem
            :route="{ name: 'dashboard' }"
            :title="$t('Home')"
            icon="house-line"
        />
        <!-- System Settings Menu -->
        <template v-if="can('show system menu')">
            <template v-if="can('show system settings menu')">
                <ModuleMenuItem
                    module="settings"
                    :moduleName="$t('Settings')"
                    icon="gear"
                >
                    <template #items>
                        <PlainMenuItem
                            v-if="can('view users')"
                            :route="{ name: 'settings.users.list' }"
                            baseRoute="settings.users"
                            :title="$t('Users')"
                        />
                        <PlainMenuItem
                            v-if="can('view languages')"
                            :route="{ name: 'settings.languages.list' }"
                            baseRoute="settings.languages"
                            :title="$t('Languages')"
                        />
                    </template>
                </ModuleMenuItem>
            </template>
        </template>
        <!-- Academics Menu -->
        <template v-if="can('show academic menu')">
            <ModuleMenuItem
                module="academics"
                :moduleName="$t('Academics')"
                icon="graduation-cap"
            >
                <template #items>
                    <template v-if="can('show academic settings menu')">
                        <!-- Academics Settings Menu-->
                        <SubmoduleMenuItem
                            submodule="settings"
                            :submoduleName="$t('Settings')"
                            icon="sliders"
                        >
                            <template #items>
                                <PlainMenuItem
                                    v-if="can('view language levels')"
                                    :route="{ name: 'academics.settings.language-levels.list' }"
                                    baseRoute="academics.settings.language-levels"
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
                                    v-if="can('view class schedules')"
                                    :route="{ name: 'academics.classes.class-schedules.list' }"
                                    baseRoute="academics.classes.class-schedules"
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
import { useAuthStore } from '@/stores/auth';
import { usePermissions } from '@/composables/usePermissions';
import IconMenuItem from './IconMenuItem.vue';
import ModuleMenuItem from './ModuleMenuItem.vue';
import SubmoduleMenuItem from './SubmoduleMenuItem.vue';
import PlainMenuItem from './PlainMenuItem.vue';

const auth = useAuthStore();
const { can } = usePermissions();

</script>