package pt.ipleiria.estg.dei.amsi.cinelive.listeners;

import java.util.List;
import java.util.Map;

import pt.ipleiria.estg.dei.amsi.cinelive.models.Sessao;

public interface SessoesListener {
    void onSuccess(Map<String, List<Sessao>> sessoesPorData);
    void onError();
}
