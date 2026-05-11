class Validator {

    static required(value, label = 'Field') {

        value = value?.trim();

        if (!value) {
            throw new Error(`${label} is required`);
        }

        return value;
    }

    static email(value) {

        value = this.required(value, 'Email');

        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!regex.test(value)) {
            throw new Error('Invalid email format');
        }

        return value;
    }

    static password(value) {

        value = this.required(value, 'Password');

        if (value.length < 6) {
            throw new Error('Password must be at least 6 characters');
        }

        return value;
    }

    static name(value, label = 'Name') {

        value = this.required(value, label);

        if (value.length < 2) {
            throw new Error(`${label} must be at least 2 characters`);
        }

        return value;
    }

    static integer(value, label = 'Value', min = 0) {

        if (value === '' || value === null || value === undefined) {
            throw new Error(`${label} is required`);
        }

        value = parseInt(value);

        if (isNaN(value)) {
            throw new Error(`${label} must be an integer`);
        }

        if (value < min) {
            throw new Error(`${label} must be at least ${min}`);
        }

        return value;
    }

    static phone(value) {

        if (!value) {
            return null;
        }

        value = value.trim();

        if (!/^[0-9]{10}$/.test(value)) {
            throw new Error('Invalid phone number');
        }

        return value;
    }
}