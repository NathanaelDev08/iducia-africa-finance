import { useEffect } from 'react';
import { router } from '@inertiajs/react';

export default function Create() {
    useEffect(() => {
        router.visit(route('payroll.index'), { replace: true });
    }, []);
    return null;
}
