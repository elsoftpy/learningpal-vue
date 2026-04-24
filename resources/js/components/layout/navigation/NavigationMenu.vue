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
                                <PlainMenuItem
                                    v-if="can('view courses')"
                                    :route="{ name: 'academics.settings.courses.list' }"
                                    baseRoute="academics.settings.courses"
                                    :title="$t('Courses')"
                                />
                                <PlainMenuItem
                                    v-if="can('view study programs')"
                                    :route="{ name: 'academics.settings.study-programs.list' }"
                                    baseRoute="academics.settings.study-programs"
                                    :title="$t('Study Programs')"
                                />
                                <PlainMenuItem
                                    v-if="can('view teachers')"
                                    :route="{ name: 'academics.settings.teachers.list' }"
                                    baseRoute="academics.settings.teachers"
                                    :title="$t('Teachers')"
                                />
                                <PlainMenuItem
                                    v-if="can('view students')"
                                    :route="{ name: 'academics.settings.students.list' }"
                                    baseRoute="academics.settings.students"
                                    :title="$t('Students')"
                                />
                                <PlainMenuItem
                                    v-if="can('view level contents')"
                                    :route="{ name: 'academics.settings.level-contents.list' }"
                                    baseRoute="academics.settings.level-contents"
                                    :title="$t('Level Contents')"
                                />
                            </template>
                        </SubmoduleMenuItem>
                    </template>
                    <!-- Academic Classes Menu-->
                    <SubmoduleMenuItem
                        v-if="can('show academic classes menu')"
                        submodule="classes"
                        :submoduleName="$t('Classes')"
                        icon="chalkboard-teacher"
                    >
                        <template #items>
                            <PlainMenuItem
                                v-if="can('view own class schedule details')"
                                :route="{ name: 'academics.classes.class-schedules.my-sessions' }"
                                baseRoute="academics.classes.class-schedules.my-sessions"
                                :title="$t('My Class Schedules')"
                            />
                            <PlainMenuItem
                                v-if="can('view class records')"
                                :route="{ name: 'academics.classes.class-records.list' }"
                                baseRoute="academics.classes.class-records"
                                :title="$t('Class Records')"
                            />
                            <PlainMenuItem
                                v-if="can(['view assigned distance activities', 'view own distance activities', 'view all distance activities'])"
                                :route="{ name: 'academics.classes.distance-activities.list' }"
                                baseRoute="academics.classes.distance-activities"
                                :title="$t('Distance Activities')"
                            />
                            <PlainMenuItem
                                v-if="can('view class schedules')"
                                :route="{ name: 'academics.classes.class-schedules.list' }"
                                baseRoute="academics.classes.class-schedules"
                                :title="$t('Class Schedules')"
                            />
                        </template>
                    </SubmoduleMenuItem>

                    <SubmoduleMenuItem
                        v-if="can('show reports menu')"
                        submodule="reports"
                        :submoduleName="$t('Reports')"
                        icon="chart-line"
                    >
                        <template #items>
                            <PlainMenuItem
                                v-if="can('view monthly classes report')"
                                :route="{ name: 'academics.reports.monthly-classes' }"
                                baseRoute="academics.reports"
                                :title="$t('Monthly Classes')"
                            />
                            <PlainMenuItem
                                v-if="can('view teacher hours report')"
                                :route="{ name: 'academics.reports.teacher-hours' }"
                                baseRoute="academics.reports"
                                :title="$t('Teacher Hours')"
                            />
                        </template>
                    </SubmoduleMenuItem>
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
