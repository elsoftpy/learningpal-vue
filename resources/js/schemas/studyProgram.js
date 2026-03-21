import { z } from 'zod';

export const createStudyProgramSchema = (t) => {
    const activitySchema = z.object({
        level_content_id: z.number().nullable().optional(),
        free_content: z.string().nullable().optional(),
        activity_name: z.string().min(1, t('Activity name is required.')),
        type: z.string().min(1, t('Activity type is required.')),
        links: z.string().nullable().optional(),
        sort_order: z.number().int().min(1, t('Sort order must be at least 1.')),
    }).superRefine((activity, ctx) => {
        const hasLevelContent = activity.level_content_id !== null && activity.level_content_id !== undefined;
        const hasFreeContent = typeof activity.free_content === 'string' && activity.free_content.trim().length > 0;

        if (hasLevelContent && hasFreeContent) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                path: ['free_content'],
                message: t('Free content must be empty when content topic is selected.'),
            });
        }

        if (!hasLevelContent && !hasFreeContent) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                path: ['free_content'],
                message: t('Free content is required when no content topic is selected.'),
            });
        }

        const hasLinks = typeof activity.links === 'string' && activity.links.trim().length > 0;

        if (activity.type === 'video' && !hasLinks) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                path: ['links'],
                message: t('A video activity requires at least one link.'),
            });
        }
    });

    const weekSchema = z.object({
        week_number: z.number().int().min(1, t('Week number must be at least 1.')),
        title: z.string().min(1, t('Week title is required.')),
        status: z.string().min(1, t('Week status is required.')),
        activities: z.array(activitySchema).min(1, t('Each week must contain at least one activity.')),
    });

    return z.object({
        language_id: z.number().min(1, t('Language is required.')),
        language_level_id: z.number().min(1, t('Language level is required.')),
        title: z.string().min(1, t('Study program title is required.')),
        status: z.string().min(1, t('Status is required.')),
        weeks: z.array(weekSchema).min(1, t('Add at least one week to the study program.')),
    });
};
