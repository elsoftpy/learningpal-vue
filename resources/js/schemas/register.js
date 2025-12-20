import { z } from 'zod';

export const createRegisterSchema = (t) => {
  return z.object({
    personal_id: z.string().min(1, t('Personal ID is required.')),
    first_name: z.string().min(1, t('First Name is required.')),
    last_name: z.string().min(1, t('Last Name is required.')),
    phone: z.string().optional(),
    email: z.email(t('Invalid email address.'))
      .min(1, t('Email is required.')),
    password: z.string().min(6, t('Password must be at least 6 characters long.')),
    password_confirmation: z.string().min(1, t('Password Confirmation is required.')),
  }).refine((data) => data.password === data.password_confirmation, {
    message: t('Passwords do not match.'),
    path: ['password_confirmation'],
  });
};