import prisma from './src/config/prisma';

async function test() {
    const users = await prisma.user.findMany({
        select: {
            id: true,
            name: true,
            teamMembers: {
                select: {
                    team: { select: { id: true, teamName: true, leaderId: true } }
                }
            }
        }
    });

    const ratings = await prisma.rating.findMany({
        where: { periodMonth: new Date().getMonth() + 1, periodYear: new Date().getFullYear() },
        select: { targetUserId: true, evaluatorId: true, score: true }
    });

    console.log("Ratings:");
    console.log(ratings);

    for (const user of users) {
        if (user.name === "Ani Pegawai") {
            const userTeams = user.teamMembers.map(tm => tm.team);
            console.log("\nAni's Teams:");
            console.log(userTeams);

            userTeams.forEach(team => {
                const teamRating = ratings.find(r => r.targetUserId === user.id && r.evaluatorId === team.leaderId);
                console.log(`Team: ${team.teamName}, Leader: ${team.leaderId}, matchedRating:`, teamRating);
            });
        }
    }
}
test().then(() => console.log("Done"));
