<?php

class Cli_Model_CastleNameGenerator
{
    private $_castleName = array("Dragonspire", "Redmont", "Farrador", "Dannamore", "Windamere", "Braewood",
        "Perrigwyn", "Cantlyn", "Tessaway", "Brawnlyn", "Aeskrow", "Balling", "Boltan", "Boltangate", "Caestshire",
        "Celnaer", "Slyborn", "Calbridge", "Dewmire", "Craester Arms", "Croglang", "Darton", "Darenby", "Dunstead",
        "Shardore", "Goodmond", "Salkire", "Hordrigg", "Hopeshire", "Haerton", "Cullin", "Murton", "Iredale", "Cornby",
        "Croilton", "Kirkoswald", "Levans", "Little Cardle", "Carderby", "Ormshire", "Dockerly", "Pierceton",
        "Crandalholme", "Faerchester", "Sella", "Skelside", "Selsmire", "Staerdale", "Direwood", "Waernell",
        "Worthwood", "Wilton", "Bellbroke", "Brivey", "Breuce", "Ashington", "Haword", "Clifton", "Highcalere",
        "Mireworth", "New Wandour", "Bornesher", "Werth", "Wishborne", "Arcton", "Allerton", "Auglire", "Avolire",
        "Bellton", "Bilesworth", "Bode", "Aedon", "Garring", "Baedcove", "Crireton", "Cloveshire", "Custaeton",
        "Crachton", "Droskyn", "Elkmire", "Ernmore", "Uwile", "Farleigh", "Harley", "Werthingham", "Zatherop",
        "Blire", "Pradingly", "Highburn", "Hillfield", "Kernwith", "Cowle", "Knaerwood", "Nascombe", "Midford",
        "Malgrave", "Otterberg", "Kentillie", "Reave", "Ryre", "St. Clare", "Sipdon", "Seanton", "Santhope", "Dudley",
        "Swanton", "Streganna", "Wardhurst", "Whitehaven", "Wattingham", "Whitstone", "Wallersley", "Willbridge",
        "Appley", "Baldon", "Blaise", "Bolltree", "Baston", "Bryalshire", "Broadcove", "Castlebourne", "Clarn",
        "Clapton", "Dinton", "Draydon", "Darnstall", "Dustorn", "Portam", "Headow", "Garley", "Naesbrey", "Parton",
        "Redford", "Yardway", "Weavington", "Cladborough", "Parthley", "Rundhey", "Bargsea", "Sevenberg", "Shaldorn",
        "Starm", "Saelmere", "Nightwell", "Starnborough", "Stowe", "Strathenberg", "Sandorne", "Wardford",
        "Bangleswade", "Baltso", "Cainhorn", "Chilgrave", "Eastcairn", "Galbury", "Flatwick", "Hingham", "Cardell",
        "Cordington", "Ranhold", "Rissingshire", "Khurleigh", "Talsworth", "Tarlington", "Cottenhorn", "Yielden",
        "Sangeries", "Barthmont", "Dewbury", "Hampstead", "Yorthendon", "Darlington", "Windsor", "Calber", "Pardwell",
        "Cunningham", "Laventhorpe", "Cublerton", "Broadborough", "Eallesborough", "Arvendon", "Karmble", "Marseden",
        "Tarville", "Wolveshire", "Coarshire", "Alderth", "Borun", "Hurwell", "Lambridge", "Charvaley", "Earlton",
        "Ely", "Hartington", "Carsley", "Catterborough", "Warltonwood", "Larton", "Elden", "Cambolton", "Mortling",
        "Fanthorpe", "Farnborough", "Croftvalley", "Eldford", "Dandlestone", "Faerdham", "Gourdley", "Merclefield",
        "Goulpass", "Craentich", "Norhall", "Whitich", "Paelford", "Corlach", "Adwick", "Sparrington", "Baerston",
        "Chastershire", "Chourmondeley", "Dordington", "Hurlton", "Parkforton", "Coltherstone", "Calden", "Cadworth",
        "Startlam", "Aeckland", "Bawres", "Barnacton", "Darham", "Lorton", "Faemley", "Mortham", "Scarwood", "Wulworth",
        "Witton", "Boussiney", "Borthrough", "Curdingham", "Harlston", "Arpton", "Pernstow", "Caerhayes", "Curnbrey",
        "Faerseton", "Parandor", "Fangdor", "Eastormel", "Artanges", "Termarth", "Oldingham", "Howers", "Aegremonth",
        "Haeresceugh", "Haertley", "Ayes", "Carcoswald", "Lamberside", "Lardel", "Merryport", "Perlington",
        "Staedbergh", "Tortmain", "Ardleby", "Armathain", "Earnside", "Easkerton", "Bartham", "Barncowl", "Barkenburgh",
        "Brackhill", "Barthwaite", "Bourgh", "Borugham", "Burneside", "Carlisle", "Gatterlen", "Clafton", "Ackermouth",
        "Carby", "Bacre", "Hartlon", "Warington", "Darwaeton", "Darrumburgh", "Harbyborough", "Hayton", "Harzelslack",
        "Hewgill", "Haarton", "Aysel", "Kaerndal", "Karthmere", "Carnstock", "Fowther", "Middleborough", "Gancaster",
        "Naeworth", "Newbining", "Pendragon", "Enrilth", "Rose", "Scatterby", "Mizeareigh", "Torpin", "Aebarrow",
        "Withall", "Arltington", "Wray", "Yeanworth", "Lakewell", "Darbey", "Daffield", "Galsop", "Garsley",
        "Heathersage", "Helmesfield", "Oakenfield", "Haersley", "Calbourne", "Mearley", "Palsbury", "Bellsover",
        "Candor", "Elverston", "Headdon", "Merkworth", "Parverhill", "Raebershire", "Wringcaster", "Harmpton",
        "Bernstaple", "Darpley", "Blackdown", "Eagleview", "Harwood", "Oakwell", "Millford", "Tarsington", "Caenleigh",
        "Eaveton", "Pomparley", "Backleigh", "Dawnton", "Darthill", "Dorgoil", "Gadleigh", "Heamyock", "Kingshill",
        "Leydford", "Merliscire", "Oakhampton", "Plympford", "Sraederham", "Rouguemont", "Talverton", "Waelcombe",
        "Taetnire", "Moldermouth", "Lorechester", "Callborough", "Marshwood", "Elderstock", "Riverfoot", "Starminster",
        "Waerham", "Bellesea", "Corftey", "Raefus", "Woodsford", "Eaghton", "Heathyard", "Yanborough", "Darfield",
        "Maetrine", "Warsle", "Perlsea", "Glottenham", "Islefield", "Bordium", "Herstings", "Laeves", "Pevanshire",
        "Rye", "Capvering", "Cainfield", "Angarth", "Baerth", "Pelsley", "Carleigh", "Calchester", "Cadleigh",
        "Hardingham", "Waerlden", "Barmsfield", "Harle", "Sirenchester", "Barknor", "Howlester", "Fowlsfield",
        "Brittlebean", "Miserth", "Narlington", "Ruarden", "Carneath", "Greenhill", "Tarnton", "Wenchcombe", "Barkely",
        "Beverstone", "Barviel", "Stadely", "Tornbury", "Alterwood", "Starkport", "Rachdale", "Dornham", "Lanchester",
        "Backton", "Arlcliff", "Calsley", "Herst", "Nartley", "Ardiham", "Forechester", "Windshire", "Wolvesley",
        "Almerry", "Ashtanshire", "Barnsil", "Berdwardshire", "Dorston", "Ardismouth", "Barlishmire", "Eryas",
        "Haerford", "Kalepeck", "Langen", "Lyonhall", "Mercle", "Arcop", "Fernyard", "Stappleton", "Warcton",
        "Burmstone", "Barmpton", "Calford", "Croft", "Dawnton", "Goulrich", "Cannersly", "Permbridge", "Stonehill",
        "Wintershold", "Windkeep", "Archdale", "Treehold", "Summerswind", "Ultrona", "Langdale", "Longdale",
        "Bruckstone", "Euthoria", "Azgul", "Stormholme", "Riverdale", "Ulentor", "Mirador", "Bundor", "Gandum",
        "Mandoom", "Daroonga", "Grimtol", "Gumtar", "Muria", "Maelony", "Galadhor", "Gundor", "Logoria", "Taergoria",
        "Whitmore", "Warlton", "Arnstey", "Berlington", "Starford", "Parlton", "Tharfield", "Windmontley",
        "Barkhamsted", "East Lowes", "West Lowes", "Curlisbrooke", "Narris", "Yarlmouth", "Cormwell", "Minbury",
        "Brancheley", "Falkerstone", "Queensborough", "Stowerling", "Tharnham", "Earlington", "Calterburry",
        "Chirlingstone", "Charhelm", "Eynsworth", "Leyebourne", "Saltwood", "Raychester", "Sarsinghurst", "Tornbridge",
        "Alnor", "Waelmore");
    private $_castleNameLength;
    private $_generatedNumber = null;

    public function __construct()
    {
        $this->_castleNameLength = count($this->_castleName) - 1;
    }

    public function generateCastleName()
    {
        $generatedNumber = mt_rand(0, $this->_castleNameLength);
        if ($generatedNumber === $this->_generatedNumber) {
            return $this->generateCastleName();
        }

        $this->_generatedNumber = $generatedNumber;

        return $this->_castleName[$generatedNumber];
    }
}