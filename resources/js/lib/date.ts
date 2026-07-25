import dayjs from 'dayjs';
import 'dayjs/locale/id';

dayjs.locale('id');

export function formatDate(
    value: string | Date | dayjs.Dayjs,
    format = 'DD MMM YYYY',
): string {
    return dayjs(value).locale('id').format(format);
}

export function formatDateTime(
    value: string | Date | dayjs.Dayjs,
    format = 'DD MMM YYYY HH:mm',
): string {
    return dayjs(value).locale('id').format(format);
}

export { dayjs };
