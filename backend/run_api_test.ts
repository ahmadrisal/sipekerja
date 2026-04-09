import { getPimpinanRekap } from './src/controllers/rating.controller';

async function testApi() {
    const req = { query: {} } as any;
    const res = {
        json: (data: any) => {
            const ani = data.data?.find((u: any) => u.name === 'Ani Pegawai');
            console.log(JSON.stringify(ani, null, 2));
        },
        status: (code: number) => ({ json: (d: any) => console.log(code, d) })
    } as any;
    
    await getPimpinanRekap(req, res);
}

testApi();
