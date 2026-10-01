import { describe, expect, it } from 'vite-plus/test';
import { getInitials } from './useInitials';

describe('getInitials', () => {
    it('returns an empty string for a missing or blank name', () => {
        expect(getInitials()).toBe('');
        expect(getInitials('   ')).toBe('');
    });

    it('returns the uppercased first letter of a single name', () => {
        expect(getInitials('taylor')).toBe('T');
    });

    it('uses the first and last names, ignoring middle names', () => {
        expect(getInitials('taylor alan otwell')).toBe('TO');
    });
});
