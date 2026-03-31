import { z } from 'zod';

export const createStudentSchema = (t, locale) => {
  const dataRegex = locale === 'en' ? /^\d{2}-\d{2}-\d{4}$/ : /^\d{2}\/\d{2}\/\d{4}$/;

  return z.object({
    first_name: z.string().min(1, t('First Name is required')),
    last_name: z.string().min(1, t('Last Name is required')),
    email: z.email(t('Invalid email address'))
      .min(1, t('Email is required')),
    email_alt: z.union([
      z.literal(''),
      z.email(t('Invalid email address')),
    ]).optional(),
    phone: z.string().optional(),
    address: z.string().optional(),
    birth_date: z.string()
      .refine((val) => {
        
        if (!val || val.trim() === '') return true;

        const regex = dataRegex;
        if (!regex.test(val)) return false;

        const [day, month, year] = locale === 'en' ? val.split('-') : val.split('/');
        // Note: Date constructor expects YYYY-MM-DD or MM/DD/YYYY. 
        // If locale is 'en' (MM-DD-YYYY), we can replace - with / to make it MM/DD/YYYY
        // If locale is 'es' (DD/MM/YYYY), we need to swap to MM/DD/YYYY or YYYY-MM-DD
        
        let date;
        if (locale === 'en') {
             // MM-DD-YYYY -> MM/DD/YYYY
             date = new Date(val.replace(/-/g, '/'));
        } else {
             // DD/MM/YYYY -> YYYY-MM-DD
             date = new Date(`${year}-${month}-${day}`);
        }

        return date instanceof Date && !isNaN(date);
      }, {
        message: t('Invalid date format'),
      })
      .optional(),
    courses: z.array(z.number()).min(1, t('At least one course must be selected.')),
  });
}
