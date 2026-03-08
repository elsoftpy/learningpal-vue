import { z } from 'zod';

export const createLevelContentSchema = (t) => {
  return z.object({
    language_level_id: z.number().min(1, t('Language level is required.')),
    content: z.string().min(1, t('Content is required.')),
  });
}