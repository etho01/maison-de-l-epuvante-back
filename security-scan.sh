#!/bin/bash
# Script pour analyser les vulnérabilités de sécurité dans les dépendances
# Usage: ./security-scan.sh

set -e

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔒 Security Scan - Analyse des Dépendances${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "composer.json" ]; then
    echo -e "${RED}❌ Erreur : composer.json non trouvé${NC}"
    echo "Assurez-vous d'être dans le répertoire racine du projet"
    exit 1
fi

# Vérifier que composer est installé
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Erreur : Composer n'est pas installé${NC}"
    exit 1
fi

# Vérifier que jq est installé (optionnel, pour un meilleur affichage)
JQ_AVAILABLE=false
if command -v jq &> /dev/null; then
    JQ_AVAILABLE=true
fi

echo ""
echo -e "${CYAN}📦 Étape 1/4 : Installation des dépendances${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
composer install --prefer-dist --no-progress --quiet

echo ""
echo -e "${CYAN}🔍 Étape 2/4 : Analyse des vulnérabilités (composer audit)${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Exécuter composer audit et sauvegarder le résultat
if composer audit --format=json > composer-audit.json 2>&1; then
    AUDIT_EXIT_CODE=0
else
    AUDIT_EXIT_CODE=$?
fi

# Analyser les résultats
if [ -f composer-audit.json ]; then
    if [ "$JQ_AVAILABLE" = true ]; then
        VULN_COUNT=$(cat composer-audit.json | jq -r '.advisories | length' 2>/dev/null || echo "0")
        
        if [ "$VULN_COUNT" -eq 0 ]; then
            echo -e "${GREEN}✅ Aucune vulnérabilité détectée${NC}"
        else
            echo -e "${YELLOW}⚠️  $VULN_COUNT vulnérabilité(s) détectée(s)${NC}"
            echo ""
            
            # Compter par sévérité
            CRITICAL=$(cat composer-audit.json | jq -r '[.advisories[] | select(.severity == "critical")] | length' 2>/dev/null || echo "0")
            HIGH=$(cat composer-audit.json | jq -r '[.advisories[] | select(.severity == "high")] | length' 2>/dev/null || echo "0")
            MEDIUM=$(cat composer-audit.json | jq -r '[.advisories[] | select(.severity == "medium")] | length' 2>/dev/null || echo "0")
            LOW=$(cat composer-audit.json | jq -r '[.advisories[] | select(.severity == "low")] | length' 2>/dev/null || echo "0")
            
            echo -e "${CYAN}Répartition par sévérité :${NC}"
            [ "$CRITICAL" -gt 0 ] && echo -e "  ${RED}🔴 Critical : $CRITICAL${NC}"
            [ "$HIGH" -gt 0 ] && echo -e "  ${YELLOW}🟠 High     : $HIGH${NC}"
            [ "$MEDIUM" -gt 0 ] && echo -e "  ${YELLOW}🟡 Medium   : $MEDIUM${NC}"
            [ "$LOW" -gt 0 ] && echo -e "  ${GREEN}🟢 Low      : $LOW${NC}"
            
            echo ""
            echo -e "${CYAN}Détails des vulnérabilités :${NC}"
            cat composer-audit.json | jq -r '.advisories[] | 
                "  • " + .packageName + 
                " (" + (.severity // "unknown") + ")" + 
                "\n    " + .title + 
                "\n    CVE: " + (.cve // "N/A") + 
                " | Fix: " + (.fixedVersion // "N/A") + 
                "\n"
            ' 2>/dev/null || echo "Erreur lors de l'affichage des détails"
            
            # Bloquer si vulnérabilités critiques ou hautes
            if [ $(($CRITICAL + $HIGH)) -gt 0 ]; then
                echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
                echo -e "${RED}⛔ CRITIQUE : Vulnérabilités de haute sévérité détectées !${NC}"
                echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
            fi
        fi
    else
        echo -e "${YELLOW}⚠️  jq n'est pas installé, résultat brut :${NC}"
        cat composer-audit.json
    fi
else
    echo -e "${RED}❌ Erreur : impossible de générer le rapport d'audit${NC}"
fi

echo ""
echo -e "${CYAN}📊 Étape 3/4 : Vérification des packages obsolètes${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Vérifier les packages obsolètes
if composer outdated --direct --format=json > outdated.json 2>&1; then
    OUTDATED_EXIT_CODE=0
else
    OUTDATED_EXIT_CODE=$?
fi

if [ -f outdated.json ]; then
    if [ "$JQ_AVAILABLE" = true ]; then
        OUTDATED_COUNT=$(cat outdated.json | jq -r '.installed | length' 2>/dev/null || echo "0")
        
        if [ "$OUTDATED_COUNT" -eq 0 ]; then
            echo -e "${GREEN}✅ Tous les packages sont à jour${NC}"
        else
            echo -e "${YELLOW}⚠️  $OUTDATED_COUNT package(s) obsolète(s)${NC}"
            echo ""
            
            # Afficher les 10 premiers packages obsolètes
            echo -e "${CYAN}Top 10 des packages à mettre à jour :${NC}"
            cat outdated.json | jq -r '.installed[0:10][] | 
                "  • " + .name + ": " + .version + " → " + .latest
            ' 2>/dev/null || echo "Erreur lors de l'affichage"
            
            if [ "$OUTDATED_COUNT" -gt 10 ]; then
                echo -e "${CYAN}  ... et $(($OUTDATED_COUNT - 10)) autres packages${NC}"
            fi
            
            echo ""
            echo -e "${CYAN}💡 Pour mettre à jour :${NC}"
            echo -e "   ${YELLOW}composer update --with-dependencies${NC}"
        fi
    else
        echo -e "${YELLOW}⚠️  jq n'est pas installé, résultat brut :${NC}"
        cat outdated.json
    fi
else
    echo -e "${YELLOW}⚠️  Impossible de vérifier les packages obsolètes${NC}"
fi

echo ""
echo -e "${CYAN}🛡️  Étape 4/4 : Analyse Trivy (vulnérabilités + secrets + config)${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Vérifier si Trivy est installé
TRIVY_AVAILABLE=false
if command -v trivy &> /dev/null; then
    TRIVY_AVAILABLE=true
fi

if [ "$TRIVY_AVAILABLE" = false ]; then
    # Vérifier si Docker est disponible
    if command -v docker &> /dev/null; then
        echo -e "${YELLOW}ℹ️  Trivy n'est pas installé, utilisation de Docker...${NC}"
        
        # Pull l'image Trivy
        docker pull aquasec/trivy:latest &> /dev/null
        
        # Scanner avec Trivy via Docker
        if docker run --rm -v "$(pwd):/scan" aquasec/trivy:latest fs \
            --format json \
            --output /scan/trivy-results.json \
            --severity CRITICAL,HIGH,MEDIUM,LOW \
            --scanners vuln,secret,config \
            --ignorefile /scan/.trivyignore \
            --quiet \
            /scan 2>/dev/null; then
            TRIVY_EXIT_CODE=0
        else
            TRIVY_EXIT_CODE=$?
        fi
    else
        echo -e "${YELLOW}⚠️  Trivy n'est pas installé et Docker non disponible${NC}"
        echo -e "${YELLOW}    Installation : https://aquasecurity.github.io/trivy/latest/getting-started/installation/${NC}"
        echo -e "${YELLOW}    Ou installez Docker pour utiliser Trivy via conteneur${NC}"
        TRIVY_EXIT_CODE=2
    fi
else
    echo -e "${GREEN}✅ Trivy trouvé, lancement de l'analyse...${NC}"
    
    # Scanner avec Trivy local
    if trivy fs \
        --format json \
        --output trivy-results.json \
        --severity CRITICAL,HIGH,MEDIUM,LOW \
        --scanners vuln,secret,config \
        --ignorefile .trivyignore \
        --quiet \
        . 2>/dev/null; then
        TRIVY_EXIT_CODE=0
    else
        TRIVY_EXIT_CODE=$?
    fi
fi

# Analyser les résultats Trivy
if [ -f trivy-results.json ] && [ "$JQ_AVAILABLE" = true ]; then
    TRIVY_CRITICAL=$(cat trivy-results.json | jq '[.Results[]?.Vulnerabilities[]? | select(.Severity == "CRITICAL")] | length' 2>/dev/null || echo "0")
    TRIVY_HIGH=$(cat trivy-results.json | jq '[.Results[]?.Vulnerabilities[]? | select(.Severity == "HIGH")] | length' 2>/dev/null || echo "0")
    TRIVY_MEDIUM=$(cat trivy-results.json | jq '[.Results[]?.Vulnerabilities[]? | select(.Severity == "MEDIUM")] | length' 2>/dev/null || echo "0")
    TRIVY_LOW=$(cat trivy-results.json | jq '[.Results[]?.Vulnerabilities[]? | select(.Severity == "LOW")] | length' 2>/dev/null || echo "0")
    TRIVY_SECRETS=$(cat trivy-results.json | jq '[.Results[]?.Secrets[]?] | length' 2>/dev/null || echo "0")
    TRIVY_MISCONFIGS=$(cat trivy-results.json | jq '[.Results[]?.Misconfigurations[]?] | length' 2>/dev/null || echo "0")
    
    TRIVY_TOTAL=$(($TRIVY_CRITICAL + $TRIVY_HIGH + $TRIVY_MEDIUM + $TRIVY_LOW))
    
    if [ "$TRIVY_TOTAL" -eq 0 ] && [ "$TRIVY_SECRETS" -eq 0 ] && [ "$TRIVY_MISCONFIGS" -eq 0 ]; then
        echo -e "${GREEN}✅ Aucun problème détecté par Trivy${NC}"
    else
        echo -e "${YELLOW}⚠️  Trivy a détecté des problèmes${NC}"
        echo ""
        
        if [ "$TRIVY_TOTAL" -gt 0 ]; then
            echo -e "${CYAN}Vulnérabilités détectées :${NC}"
            [ "$TRIVY_CRITICAL" -gt 0 ] && echo -e "  ${RED}🔴 Critical : $TRIVY_CRITICAL${NC}"
            [ "$TRIVY_HIGH" -gt 0 ] && echo -e "  ${YELLOW}🟠 High     : $TRIVY_HIGH${NC}"
            [ "$TRIVY_MEDIUM" -gt 0 ] && echo -e "  ${YELLOW}🟡 Medium   : $TRIVY_MEDIUM${NC}"
            [ "$TRIVY_LOW" -gt 0 ] && echo -e "  ${GREEN}🟢 Low      : $TRIVY_LOW${NC}"
            echo ""
        fi
        
        if [ "$TRIVY_SECRETS" -gt 0 ]; then
            echo -e "${RED}🔐 Secrets détectés : $TRIVY_SECRETS${NC}"
            echo -e "${YELLOW}   ⚠️  Des secrets hardcodés ont été trouvés dans le code !${NC}"
            echo ""
            
            # Afficher les secrets détectés
            cat trivy-results.json | jq -r '
                .Results[]?.Secrets[]? | 
                "   • " + .RuleID + " dans " + .Target + " (ligne " + (.StartLine | tostring) + ")"
            ' 2>/dev/null || true
            echo ""
        fi
        
        if [ "$TRIVY_MISCONFIGS" -gt 0 ]; then
            echo -e "${YELLOW}⚙️  Problèmes de configuration : $TRIVY_MISCONFIGS${NC}"
            echo ""
        fi
        
        # Afficher les vulnérabilités critiques/hautes
        if [ $(($TRIVY_CRITICAL + $TRIVY_HIGH)) -gt 0 ]; then
            echo -e "${CYAN}Top 5 des vulnérabilités critiques/hautes :${NC}"
            cat trivy-results.json | jq -r '
                [.Results[]?.Vulnerabilities[]? | select(.Severity == "CRITICAL" or .Severity == "HIGH")] | 
                .[0:5][] | 
                "   • " + .PkgName + " (" + .VulnerabilityID + ") - " + .Severity + " - " + (.Title // "N/A")
            ' 2>/dev/null || true
            echo ""
        fi
    fi
elif [ -f trivy-results.json ] && [ "$JQ_AVAILABLE" = false ]; then
    echo -e "${YELLOW}⚠️  jq n'est pas installé, résultat brut sauvegardé dans trivy-results.json${NC}"
elif [ "$TRIVY_EXIT_CODE" -ne 2 ]; then
    echo -e "${YELLOW}⚠️  Impossible d'exécuter Trivy${NC}"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Résumé final
echo ""
echo -e "${BLUE}📋 Résumé de l'analyse${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -f composer-audit.json ] && [ "$JQ_AVAILABLE" = true ]; then
    VULN_COUNT=$(cat composer-audit.json | jq -r '.advisories | length' 2>/dev/null || echo "0")
    CRITICAL=$(cat composer-audit.json | jq -r '[.advisories[] | select(.severity == "critical")] | length' 2>/dev/null || echo "0")
    HIGH=$(cat composer-audit.json | jq -r '[.advisories[] | select(.severity == "high")] | length' 2>/dev/null || echo "0")
    
    echo -e "🔒 Vulnérabilités : $VULN_COUNT"
    [ "$CRITICAL" -gt 0 ] && echo -e "   ${RED}• Critical: $CRITICAL${NC}" || true
    [ "$HIGH" -gt 0 ] && echo -e "   ${YELLOW}• High: $HIGH${NC}" || true
fi

if [ -f outdated.json ] && [ "$JQ_AVAILABLE" = true ]; then
    OUTDATED_COUNT=$(cat outdated.json | jq -r '.installed | length' 2>/dev/null || echo "0")
    echo -e "📦 Packages obsolètes : $OUTDATED_COUNT"
fi

if [ -f trivy-results.json ] && [ "$JQ_AVAILABLE" = true ]; then
    TRIVY_CRITICAL=$(cat trivy-results.json | jq '[.Results[]?.Vulnerabilities[]? | select(.Severity == "CRITICAL")] | length' 2>/dev/null || echo "0")
    TRIVY_HIGH=$(cat trivy-results.json | jq '[.Results[]?.Vulnerabilities[]? | select(.Severity == "HIGH")] | length' 2>/dev/null || echo "0")
    TRIVY_SECRETS=$(cat trivy-results.json | jq '[.Results[]?.Secrets[]?] | length' 2>/dev/null || echo "0")
    TRIVY_TOTAL=$(cat trivy-results.json | jq '[.Results[]?.Vulnerabilities[]?] | length' 2>/dev/null || echo "0")
    
    echo -e "🛡️  Trivy : $TRIVY_TOTAL vulnérabilités"
    [ "$TRIVY_CRITICAL" -gt 0 ] && echo -e "   ${RED}• Critical: $TRIVY_CRITICAL${NC}" || true
    [ "$TRIVY_HIGH" -gt 0 ] && echo -e "   ${YELLOW}• High: $TRIVY_HIGH${NC}" || true
    [ "$TRIVY_SECRETS" -gt 0 ] && echo -e "   ${RED}• Secrets: $TRIVY_SECRETS${NC}" || true
fi

echo ""
echo -e "${CYAN}📄 Rapports sauvegardés :${NC}"
[ -f composer-audit.json ] && echo "   • composer-audit.json" || true
[ -f outdated.json ] && echo "   • outdated.json" || true
[ -f trivy-results.json ] && echo "   • trivy-results.json" || true

echo ""

# Code de sortie - bloquer si vulnérabilités critiques/hautes
EXIT_CODE=0

if [ -f composer-audit.json ] && [ "$JQ_AVAILABLE" = true ]; then
    CRITICAL=$(cat composer-audit.json | jq -r '[.advisories[] | select(.severity == "critical")] | length' 2>/dev/null || echo "0")
    HIGH=$(cat composer-audit.json | jq -r '[.advisories[] | select(.severity == "high")] | length' 2>/dev/null || echo "0")
    
    if [ $(($CRITICAL + $HIGH)) -gt 0 ]; then
        echo -e "${RED}⛔ Composer Audit : Vulnérabilités critical/high détectées${NC}"
        EXIT_CODE=1
    fi
fi

if [ -f trivy-results.json ] && [ "$JQ_AVAILABLE" = true ]; then
    TRIVY_CRITICAL=$(cat trivy-results.json | jq '[.Results[]?.Vulnerabilities[]? | select(.Severity == "CRITICAL")] | length' 2>/dev/null || echo "0")
    TRIVY_HIGH=$(cat trivy-results.json | jq '[.Results[]?.Vulnerabilities[]? | select(.Severity == "HIGH")] | length' 2>/dev/null || echo "0")
    TRIVY_SECRETS=$(cat trivy-results.json | jq '[.Results[]?.Secrets[]? | select(.Severity == "CRITICAL" or .Severity == "HIGH")] | length' 2>/dev/null || echo "0")
    
    if [ $(($TRIVY_CRITICAL + $TRIVY_HIGH + $TRIVY_SECRETS)) -gt 0 ]; then
        echo -e "${RED}⛔ Trivy : Vulnérabilités critical/high ou secrets détectés${NC}"
        EXIT_CODE=1
    fi
fi

if [ "$EXIT_CODE" -eq 0 ]; then
    echo -e "${GREEN}✅ Analyse terminée avec succès${NC}"
else
    echo -e "${RED}❌ Analyse terminée avec des problèmes critiques${NC}"
fi

exit $EXIT_CODE
