import { z } from 'zod';

export const createLanguageSchema = (t) => {
  return z.object({
    name: z.string().min(1, t('Language Name is required')),
  });
}