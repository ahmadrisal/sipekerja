import 'dotenv/config';
import jwt from 'jsonwebtoken';
import http from 'http';
import prisma from './src/config/prisma';

async function testApi() {
    const pimpinan = await prisma.user.findFirst({
        where: { userRoles: { some: { role: { roleName: 'Pimpinan' } } } },
        include: { userRoles: { include: { role: true } } }
    });
    
    if (!pimpinan) return;

    const token = jwt.sign(
        { 
            id: pimpinan.id, 
            email: pimpinan.email, 
            username: pimpinan.username, 
            name: pimpinan.name,
            roles: pimpinan.userRoles.map(ur => ur.role.roleName)
        },
        process.env.JWT_SECRET || 'fallback_secret',
        { expiresIn: '1d' }
    );

    const req = http.request({
        hostname: 'localhost',
        port: 5001,
        path: '/api/ratings/pimpinan-rekap',
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'x-active-role': 'Pimpinan'
        }
    }, res => {
        let body = '';
        res.on('data', chunk => body += chunk.toString());
        res.on('end', () => {
            const data = JSON.parse(body);
            const ani = data.data?.find((u: any) => u.name === 'Ani Pegawai');
            console.log(JSON.stringify(ani, null, 2));
        });
    });

    req.end();
}
testApi();
